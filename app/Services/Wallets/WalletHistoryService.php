<?php

namespace App\Services\Wallets;

use App\Models\Aggregator;
use Throwable;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\WalletHistory;
use App\Services\CoreService;
use App\Models\PlatformWallet;
use Illuminate\Support\Facades\DB;
use App\Models\PlatformWalletHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class WalletHistoryService extends CoreService
{
    public function __construct(private \App\Services\Platforms\WalletService $platformWalletService)
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return WalletHistory::class;
    }

    /**
     * @param array $data
     * @return Model
     * @throws Throwable
     */
    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Validation pour s'assurer que les données obligatoires sont présentes
            $this->throwif(
                !data_get($data, 'type') || !data_get($data, 'amount') || !data_get($data, 'owner_uuid'),
                "Impossible de continuer, une erreur s'est produite"
            );
            // Validation pour s'assurer que le montant est supérieur à zéro
            $this->throwif(
                intval(data_get($data, 'amount')) <= 0,
                "Le montant doit être supérieur à zéro"
            );

            $owner_uuid = data_get($data, 'owner_uuid');

            // Obtenir ou créer un portefeuille pour le propriétaire spécifié
            $wallet = app(WalletService::class)->firstOrCreate(
                $owner_uuid,
                data_get($data, 'currency.id')
            );

            // Créer une nouvelle entrée d'historique pour le portefeuille
            /** @var WalletHistory $walletHistory */
            $walletHistory = $this->model()->create([
                'uuid'          => Str::uuid(),
                'type'          => data_get($data, 'type', 'withdraw'),
                'amount'        => data_get($data, 'amount'),
                'note'          => data_get($data, 'note'),
                'created_by'    => data_get($data, 'created_by', $owner_uuid),
                'status'        => data_get($data, 'status', WalletHistory::PROCESSED),
                "wallet_uuid"   => $wallet->uuid,
                "transaction_id" => $data['transaction_id'],
            ]);

            // Gérer les types de transaction : topup ou retrait
            if (data_get($data, 'type') == Wallet::TOPUP_TYPE) {
                $wallet->increment('amount', data_get($data, 'amount'));
            } else if (data_get($data, 'type') == Wallet::WITHDRAW_TYPE) {
                // Vérifier que le portefeuille a suffisamment de fonds
                $this->throwif(
                    $wallet->amount < data_get($data, 'amount'),
                    "Fonds insuffisants pour effectuer le retrait"
                );
                // Retirer les fonds du portefeuille
                $wallet->decrement('amount', data_get($data, 'amount'));
            }

            return $walletHistory;
        });
    }

    /**
     * Effectue un retrait depuis un portefeuille.
     *
     * @param array $data
     * @return WalletHistory
     * @throws Throwable
     */
    public function withdraw(array $data): WalletHistory
    {
        // Forcer le type à "withdraw"
        $data['type'] = Wallet::WITHDRAW_TYPE;

        // Réutiliser la méthode create pour gérer le retrait
        /** @var WalletHistory $walletHistory */
        return $this->create($data);
    }

    /**
     * Effectue un transfert d'un portefeuille à un autre.
     *
     * @param array $data
     * @return array
     * @throws Throwable
     */
    public function transfert(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Validation des données du transfert
            $this->throwif(
                !data_get($data, 'sender_uuid') || !data_get($data, 'receiver_uuid') || !data_get($data, 'amount'),
                "Impossible de continuer, une erreur s'est produite"
            );

            $amount = data_get($data, 'amount');
            $sender_uuid = data_get($data, 'sender_uuid');
            $receiver_uuid = data_get($data, 'receiver_uuid');

            // 1. Retirer du compte de l'expéditeur
            $withdrawData = [
                'type' => Wallet::WITHDRAW_TYPE,
                'owner_uuid' => $sender_uuid,
                'amount' => $amount,
                'currency' => data_get($data, 'currency'),
                'note' => 'Transfert vers ' . $receiver_uuid,
            ];

            $this->withdraw($withdrawData);

            // 2. Ajouter au compte du destinataire
            $topupData = [
                'type' => Wallet::TOPUP_TYPE,
                'owner_uuid' => $receiver_uuid,
                'amount' => $amount,
                'currency' => data_get($data, 'currency'),
                'note' => 'Transfert reçu de ' . $sender_uuid,
            ];

            $this->create($topupData);

            return [
                'message' => 'Transfert effectué avec succès',
                'amount' => $amount,
                'sender' => $sender_uuid,
                'receiver' => $receiver_uuid,
            ];
        });
    }

    public function createFromTransaction(Transaction $transaction)
    {
        // Vérifier si la transaction est payée
        if ($transaction->status !== Transaction::STATUS_PAID) {
            return;
        }

        // Vérifier si la transaction existe déjà dans l'historique pour éviter les fraudes
        if (WalletHistory::where('transaction_id', $transaction->id)->exists()) {
            return;
        }

        switch ($transaction->type) {
            case Transaction::TYPE_PAYMENT:
                $this->handlePaymentTransaction($transaction);
                break;

            case Transaction::TYPE_FUNDING:
                $this->handleFundingTransaction($transaction);
                break;

            case Transaction::TYPE_TRANSFERT:
                $this->handleTransferTransaction($transaction);
                break;
        }
    }

    private function handlePaymentTransaction(Transaction $transaction)
    {
        if (!$transaction->receiver_uuid) {
            if ($transaction->aggregator->isWallet()) {
                $this->platformWalletService->addFund($this->createWalletHistory(
                    $transaction->payer_uuid,
                    $transaction->amount,
                    Wallet::WITHDRAW_TYPE,
                    "Retrait pour votre paiement #{$transaction->uuid}",
                    $transaction->currency,
                    $transaction->id
                ));
            } else {

                $this->platformWalletService->createAddFund([
                    'amount' => $transaction->amount,
                    'currency_id' => $transaction->currency->id,
                    'note' => "Paiement reçu depuis la transaction #{$transaction->uuid}",
                    'transaction_id' => $transaction->id,
                    "status" => WalletHistory::PROCESSED,
                ]);
            }
        } else {
            $this->createWalletHistory(
                $transaction->receiver_uuid,
                $transaction->amount,
                Wallet::TOPUP_TYPE,
                "Paiement reçu depuis la transaction #{$transaction->uuid}",
                $transaction->currency,
                $transaction->id
            );
        }
    }

    private function handleFundingTransaction(Transaction $transaction)
    {
        $this->createWalletHistory($transaction->receiver_uuid, $transaction->amount, Wallet::TOPUP_TYPE, $transaction->note, $transaction->currency, $transaction->id);
    }

    private function handleTransferTransaction(Transaction $transaction)
    {
        if (!$transaction->receiver_uuid) {
            throw ValidationException::withMessages([
                'receiver_uuid' => 'Le destinataire est obligatoire pour un transfert.'
            ]);
        }

        $this->createWalletHistory($transaction->payer_uuid, $transaction->amount, Wallet::TOPUP_TYPE, $transaction->note, $transaction->currency, $transaction->id, $transaction->receiver_uuid);
    }

    private function createWalletHistory($ownerUuid, $amount, $type, $note, $currency, $transactionId, $receiverUuid = null)
    {
        $data = [
            "owner_uuid" => $ownerUuid,
            "amount" => $amount,
            "type" => $type,
            "note" => $note,
            "currency" => $currency,
            "transaction_id" => $transactionId,
        ];

        if ($receiverUuid) {
            $data['receiver_uuid'] = $receiverUuid;
        }

        return $this->create($data);
    }


    public function addFund(array $data)
    {
        $transaction = app(Transaction::class)->create([
            "aggregator_id" => Aggregator::wallet()->id,
            "transaction_id" => $uuid = Str::uuid(),
            "status" => Transaction::STATUS_PAID,
            "uuid" => $uuid,
            "perform_time" => now(),
            "refund_time" => null,
            'status_description' => $data['status_description'] ?? 'Add fund to user #' . $data['receiver_uuid'],
            "receiver_uuid" => $data['receiver_uuid'],
            "amount" => $data['amount'],
            "payer_uuid" => $data['payer_uuid'],
            "currency_id" => $data["currency_id"],
            'type' => Transaction::TYPE_FUNDING,
            "service_name" => "funding",
            "note" => $data["note"] ?? null,
        ]);

        return app(WalletService::class)->findBy([
            'owner_uuid' => $transaction->receiver_uuid,
            'currency_id' => $transaction->currency_id,
        ]);
    }
}
