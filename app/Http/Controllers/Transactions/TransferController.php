<?php

namespace App\Http\Controllers\Transactions;

use App\Models\Aggregator;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\AggregatorService;
use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionRequest;
use App\Http\Controllers\Transactions\CreateTransactionController;

class TransferController extends CreateTransactionController
{
    protected $type = Transaction::TYPE_PAYMENT;
    /**
     * Handle the incoming request.
     *
     * @param TransactionRequest $request
     * @param AggregatorService $aggregatorService
     * @param string $type
     * @return JsonResponse
     */
    public function __invoke(TransactionRequest $request): JsonResponse
    {
        $this->data = $request->validated();

        $request->validate([
            "receiver_uuid" => "required",
        ]);

        $this->data["receiver_uuid"] = $request->receiver_uuid;

        $aggregator = Aggregator::find($request->aggregator_id);
        if (!$aggregator->isWallet()) {
            $this->throwException("The transaction is not permitted for the aggregator service");
        }

        return $this->store($this->data);
    }
}
