<?php

namespace App\Services\Platforms;

use App\Models\Wallet;
use Illuminate\Support\Str;
use App\Models\WalletHistory;
use App\Services\CoreService;
use App\Models\PlatformWallet;
use App\Models\PlatformWalletHistory;
use Illuminate\Validation\ValidationException;

class WalletService extends CoreService
{
    protected function getModelClass()
    {
        return PlatformWalletHistory::class;
    }

    public function addFund(WalletHistory $history)
    {
        $this->createAddFund([
            'amount' => $history->amount,
            'note' => $history->note,
            'transaction_id' => $history->transaction->id,
            'currency_id' => $history->transaction->currency_id
        ]);
    }

    public function withdrawFund(WalletHistory $history)
    {
        $this->createWithdrawFund([
            'amount' => $history->amount,
            'note' => $history->note,
            'transaction_id' => $history->transaction->id,
            'currency_id' => $history->transaction->currency_id
        ]);
    }

    public function createAddFund(array $data)
    {
        $this->create([
            'type' => Wallet::TOPUP_TYPE,
        ] + $data);
    }

    private function createWithdrawFund(array $data)
    {
        return $this->create([
            'type' => Wallet::WITHDRAW_TYPE,
        ] + $data);
    }

    public function wallet($currency_id): PlatformWallet
    {
        // Récupérer le wallet de la plateforme pour la devise de la transaction
        $platformWallet = PlatformWallet::where('currency_id', $currency_id)->first();

        if (!$platformWallet) {
            throw ValidationException::withMessages([
                'currency' => 'Le wallet de la plateforme pour cette devise n\'existe pas.'
            ]);
        }

        return $platformWallet;
    }

    public function create(array $data)
    {
        $platformWallet = $this->wallet($data['currency_id']);
        $history = parent::create([
            'uuid' => Str::uuid(),
            'platform_wallet_id' => $platformWallet->id,
            'type' => $data['type'],
            'amount' => $data['amount'],
            'note' => $data['note'],
            'status' => data_get($data, 'status', WalletHistory::PROCESSED),
            'transaction_id' => $data['transaction_id'],
        ]);

        if ($history->status == WalletHistory::PROCESSED) {
            if ($data['type'] === Wallet::TOPUP_TYPE) {
                $platformWallet->increment('amount', $data['amount']);
            } else {
                $platformWallet->decrement('amount', $data['amount']);
            }
        }
        return $history;
    }
}
