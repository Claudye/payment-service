<?php

namespace App\Services;

use App\Models\Aggregator;
use App\Traits\CanTrowException;
use App\Interfaces\TransactionInterface;
use App\Interfaces\AggregatorSericeInterface;

class AggregatorService
{
    use CanTrowException;
    protected $aggregators = [
        "fedapay" =>  \App\Services\Aggregators\FedapayService::class,
        'wallet' => \App\Services\Aggregators\WalletAggregator::class
    ];
    /**
     * Aggregator service
     *
     * @var AggregatorSericeInterface
     */
    protected $service;


    public function init($aggregator = null)
    {
        if (!($aggregator instanceof Aggregator)) {
            $aggregator = Aggregator::find($aggregator ?? request('aggregator_id'));
        }

        $this->throwif(!$aggregator, "L'aggregator $aggregator n'est pas pris en charge");
        $this->throwif(!$aggregator->active, "L'aggregator $aggregator n'est pas actif");

        $class = data_get($this->aggregators, $aggregator->slug);
        if (!$class) {
            throw new \Exception("L'aggregator $aggregator n'est pas pris en charge");
        }
        $this->service = app($class);

        $this->service->setModel($aggregator);
        $this->service->init($aggregator->env_keys);
    }

    public function service(): AggregatorSericeInterface
    {
        return $this->service;
    }

    public function verify($transactionId, ?callable $fails = null): TransactionInterface
    {
        try {
            return $this->service()
                ->verify($transactionId);
        } catch (\Throwable $th) {
            if ($fails) {
                return $fails($th);
            }
            throw $th;
        }
    }

    public function slugs(): array
    {
        return Aggregator::slugs();
    }

    public function actives()
    {
        return Aggregator::where('active', true);
    }

    public function createTransaction(array $data)
    {

        return $this->service()->createTransaction($data);
    }

    public function isWallet()
    {
        return $this->service()->getModel()->isWallet();
    }
}
