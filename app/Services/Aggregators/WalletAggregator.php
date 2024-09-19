<?php

namespace App\Services\Aggregators;

use App\Helpers\GetTransaction;
use App\Interfaces\AggregatorSericeInterface;
use App\Models\Transaction;
use App\Services\Transactions\TransactionService;
use App\Services\Wallets\WalletService;
use Illuminate\Validation\ValidationException;

class WalletAggregator implements AggregatorSericeInterface
{
    private $model =  null;
    public function verify($transactionId): \App\Interfaces\TransactionInterface
    {

        $transaction = app(TransactionService::class)->findOne("transaction_id", $transactionId);

        if (!$transaction) {
            throw new \Exception("La transaction $transactionId n'existe pas");
        }

        dd($transaction);

        return new GetTransaction(
            $transactionId,
            $transaction->amount,
            $transaction->status,
        );
    }

    public function getSlug(): string
    {
        return 'wallet';
    }

    public function init(array $keys) {}

    public function getModel(): \App\Models\Aggregator|null
    {
        return $this->model;
    }

    public function setModel(?\App\Models\Aggregator $model = null): AggregatorSericeInterface
    {
        $this->model = $model;
        return $this;
    }

    public function createTransaction(array $data): GetTransaction
    {
        $payerUuid = $data['payer_uuid'] ?? null;
        if (!$payerUuid) {
            throw ValidationException::withMessages([
                'payer_uuid' => "Le payer uuid est requis",
            ]);
        }
        $wallet = app(WalletService::class)->findBy([
            'owner_uuid' => $payerUuid,
            'currency_id' => data_get($data, 'currency.id'),
        ]);

        if (!$wallet) {
            throw ValidationException::withMessages([
                'payer_uuid' => "Aucun portefeuille trouvé pour le payeur $payerUuid",
            ]);
        }

        $amount = data_get($data, "amount");

        if ($wallet->amount < $amount) {
            throw ValidationException::withMessages([
                'amount' => "Le portefeuille du $payerUuid ne dispose pas assez de fonds",
            ]);
        }

        return new GetTransaction(
            data_get($data, 'uuid', null),
            $amount,
            Transaction::STATUS_PAID,
        );
    }
}
