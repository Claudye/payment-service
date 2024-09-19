<?php

namespace App\Services\Aggregators;

use App\Models\Aggregator;
use App\Helpers\GetTransaction;
use App\Interfaces\TransactionInterface;
use App\Interfaces\AggregatorSericeInterface;
use App\Traits\CanTrowException;

class FedapayService implements AggregatorSericeInterface
{
    use CanTrowException;
    private $model =  null;
    public function verify($transactionId): TransactionInterface
    {
        $transaction = \FedaPay\Transaction::retrieve($transactionId);

        return new GetTransaction(
            data_get($transaction, "id"),
            data_get($transaction, "amount"),

            $this->getStatus(data_get($transaction, "status"))
        );
    }



    public function getSlug(): string
    {
        return "fedapay";
    }

    public function init(array $env)
    {
        $private_key = data_get($env, "private_key");
        $env_key = data_get($env, "env");
        // Initialisation du SDK FedaPay
        \FedaPay\FedaPay::setApiKey(env($private_key));
        \FedaPay\FedaPay::setEnvironment(env($env_key));
    }

    public function getModel(): ?Aggregator
    {
        return $this->model;
    }

    public function setModel(?Aggregator $model = null): AggregatorSericeInterface
    {
        $this->model = $model;
        return $this;
    }

    public function createTransaction(array $data): GetTransaction
    {
        $customer = data_get($data, 'customer', []);

        $amount = intval(data_get($data, "amount"));

        if (!is_integer($amount)) {
            $this->throwValidationErros([
                "amount" => "Le montant doit être un entier"
            ]);
        }

        /* Create the transaction */
        $transaction = \FedaPay\Transaction::create(array(
            "description" => "Paiement à " . env('APP_NAME'),
            "amount" => $amount,
            "currency" => ["iso" => strtoupper($data['currency']['code'])],
            "callback_url" => $data['callback_url'],
        ) + $customer);

        $tokens = $transaction->generateToken();
        return new GetTransaction(
            data_get($transaction, "id"),
            data_get($transaction, "amount"),
            $this->getStatus(data_get($transaction, "status")),
            [
                "payment_url" => data_get($tokens, 'url')
            ]
        );
    }

    public function getStatus($status)
    {

        return match ($status) {
            "approved" =>  \App\Models\Transaction::STATUS_PAID,
            default =>  \App\Models\Transaction::STATUS_PROGRESS,
        };
    }
}
