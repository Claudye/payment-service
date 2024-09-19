<?php

namespace App\Http\Controllers\Transactions;

use App\Models\Currency;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Services\AggregatorService;
use App\Http\Controllers\Controller;
use App\Interfaces\TransactionInterface;
use App\Http\Requests\TransactionRequest;
use App\Services\Transactions\TransactionService;

class CreateTransactionController extends Controller
{
    protected $type = "";

    protected array $data = [];

    public function __construct(protected AggregatorService $aggregatorService) {}
    /**
     * Handle the incoming request.
     *
     * @param TransactionRequest $request
     * @param AggregatorService $aggregatorService
     * @param string $type
     * @return JsonResponse
     */
    public function store(array $data): JsonResponse
    {
        $this->data += $data;

        $this->initializeAggregator();

        $uuid = Str::uuid();
        $this->data['type'] = $this->type;
        $this->data['uuid'] = $uuid;
        $this->data["currency"] = Currency::find($this->data['currency_id']);
        $this->data["callback_url"] = route("transactions.proceed", [
            "transaction" => $uuid,
            "type" => $this->type
        ]);

        return $this->processTransaction();
    }


    /**
     * Initialize the aggregator service.
     *
     */
    private function initializeAggregator(): void
    {
        $this->aggregatorService->init($this->data['aggregator_id']);
    }


    /**
     * Process the transaction with the aggregator service.
     *
     * @param AggregatorService $aggregatorService
     * @param array $data
     * @param string $uuid
     * @return JsonResponse
     */
    private function processTransaction(): JsonResponse
    {
        return $this->promise(function () {
            return $this->aggregatorService->createTransaction($this->data);
        }, times: 3, sleep: 5)
            ->then(function (TransactionInterface $aggregatorTransaction) {
                return $this->createTransactionRecord($this->data, $aggregatorTransaction, $this->aggregatorService, $this->data['uuid']);
            }, commit: true)
            ->catch(function ($th) {
                return $this->handleTransactionError($th);
            })
            ->jsonResponse();
    }

    /**
     * Create a transaction record in the database.
     *
     * @param array $data
     * @param TransactionInterface $aggregatorTransaction
     * @param AggregatorService $aggregatorService
     * @param string $uuid
     * @return array
     */
    private function createTransactionRecord(array $data, TransactionInterface $aggregatorTransaction, AggregatorService $aggregatorService, $uuid): array
    {
        $data += [
            "aggregator_id" => $aggregatorService->service()->getModel()->id,
            "transaction_id" => $aggregatorService->isWallet() ? $uuid : $aggregatorTransaction->getId(),
            "status" => $aggregatorTransaction->getStatus(),
            "uuid" => $uuid,
            "perform_time" => null,
            "refund_time" => null,
            'status_description' => $data['status_description'] ?? 'Transaction in progress',
            "receiver_uuid" => $data['receiver_uuid'] ?? null
        ];

        $transaction = app(TransactionService::class)->create($data);

        return [
            "uuid" => $uuid,
            "status" => $transaction->status,
            "type" => $transaction->type,
            "verification_url" => route('transactions.verify', [
                "transaction" => $uuid,
                "type" => $transaction->type
            ]),
            "payment_url" => $aggregatorTransaction->options('payment_url'),
            "transaction_id" => $transaction->transaction_id
        ];
    }

    /**
     * Handle errors that occur during transaction processing.
     *
     * @param mixed $th
     * @return bool
     */
    private function handleTransactionError($th): bool
    {
        if ($th instanceof \Illuminate\Validation\ValidationException) {
            return false;
        }
        return true;
    }

    protected function insertData(string $name, $value)
    {
        $this->data[$name] = $value;
        return $this;
    }
}
