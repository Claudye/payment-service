<?php

namespace App\Helpers;

use App\Interfaces\TransactionInterface;

class GetTransaction implements TransactionInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private $id,
        private int $amount,
        private string $status,
        private array $options = [],
    ) {
        //
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getUser(): array
    {
        return [];
    }

    public function getEmail(): ?string
    {
        return null;
    }
    public function getPhone(): ?string
    {
        return null;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function options(?string $keys = null): mixed
    {
        if ($keys) {
            return data_get($this->options, $keys, null);
        }
        return $this->options;
    }
}
