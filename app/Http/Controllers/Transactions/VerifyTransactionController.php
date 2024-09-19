<?php

namespace App\Http\Controllers\Transactions;

use App\Helpers\Resp;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Services\AggregatorService;

class VerifyTransactionController extends Controller
{
    public function __invoke($type, Transaction $transaction,  AggregatorService $aggregatorService)
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
            $aggregatorTransaction = $aggregatorService->verify($transaction->transaction_id);
            if ($aggregatorTransaction->getStatus() !=  Transaction::STATUS_PROGRESS) {
                return app(ProceedController::class)->__invoke(
                    $transaction,
                    $transaction->type,
                    $aggregatorService
                );
            }
            throw new \Exception("Paiement échoué");
        }, times: 5, sleep: 10000)->jsonResponse();
    }
}
