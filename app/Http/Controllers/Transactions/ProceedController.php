<?php

namespace App\Http\Controllers\Transactions;

use App\Helpers\Resp;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Services\AggregatorService;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Services\Transactions\TransactionService;

class ProceedController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($type, Transaction $transaction, AggregatorService $aggregatorService)
    {
        $this->throwif(
            !in_array($type, Transaction::TYPES),
            "Page not found",
            code: 404
        );

        if ($transaction->status !== Transaction::STATUS_PROGRESS) {

            return Resp::success(TransactionResource::make($transaction));
        }

        $aggregatorService->init($transaction->aggregator);

        return $this->promise(function () use ($aggregatorService, $transaction) {
            return $aggregatorService->verify($transaction->transaction_id);
        }, times: 5, sleep: 10)->then(function ($aggregatorTransaction) use ($transaction) {

            if ($transaction->amount != $aggregatorTransaction->getAmount()) {
                return Resp::error("Amount mismatch", statuscode: 419);
            }
            app(TransactionService::class)->update($transaction, [
                "status" => $aggregatorTransaction->getStatus(),
                "perform_time" => now()->toIso8601String()
            ]);

            return TransactionResource::make($transaction);
        }, commit: true)->jsonResponse();
    }
}
