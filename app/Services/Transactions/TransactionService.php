<?php

namespace App\Services\Transactions;


use App\Models\Transaction;
use App\Services\CoreService;
use App\Services\WalletHistoryService;

class TransactionService extends CoreService
{

    public function __construct(
        private WalletHistoryService $walletHistoryService,
    ) {
        parent::__construct();
    }
    public function getModelClass()
    {
        return Transaction::class;
    }


    public function create(array $attributes)
    {
        return parent::create($attributes);
    }
}
