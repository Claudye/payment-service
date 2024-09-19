<?php

namespace App\Http\Controllers\Transactions;

use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use App\Services\AggregatorService;
use App\Http\Requests\TransactionRequest;

class WithdrawController extends CreateTransactionController
{
    protected $type = Transaction::TYPE_WITHDRAW;
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
        return $this->store($request->validated());
    }
}
