<?php

namespace App\Interfaces;

use App\Models\Aggregator;
use App\Helpers\GetTransaction;
use Illuminate\Database\Eloquent\Model;
use App\Interfaces\TransactionInterface;

interface AggregatorSericeInterface
{
    public function verify($transactionId): TransactionInterface;
    public function getSlug(): string;

    public function init(array $keys);

    public function getModel(): Aggregator | null;
    public function setModel(?Aggregator $model = null): AggregatorSericeInterface;

    public function createTransaction(array $data): GetTransaction;
}
