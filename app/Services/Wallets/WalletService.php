<?php

namespace App\Services\Wallets;

use App\Models\Wallet;
use Illuminate\Support\Str;
use App\Services\CoreService;

class WalletService extends CoreService
{
    protected function getModelClass()
    {
        return Wallet::class;
    }

    public function firstOrCreate($owner_uuid, $currency_id): Wallet
    {
        $wallet = $this->findBy($data = [
            'owner_uuid' => $owner_uuid,
            'currency_id' => $currency_id,
        ]);

        if (!$wallet) {
            return $this->model()->create($data + ['amount' => 0, "uuid" => Str::uuid()]);
        }

        return $wallet;
    }
}
