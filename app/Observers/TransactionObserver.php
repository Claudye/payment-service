<?php

namespace App\Observers;

use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\WalletHistory;
use App\Services\Wallets\WalletHistoryService;
use Illuminate\Validation\ValidationException;

class TransactionObserver
{
    public function __construct(protected WalletHistoryService $walletHistoryService) {}
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        $this->handleTransactionStatus($transaction);
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        $this->handleTransactionStatus($transaction);
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }
    /**
     * Vérifie et gère les transactions avec un statut payé.
     */
    protected function handleTransactionStatus(Transaction $transaction)
    {
        $this->walletHistoryService->createFromTransaction($transaction);
    }
}
