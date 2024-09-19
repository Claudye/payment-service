<?php

namespace App\Interfaces;

interface TransactionInterface
{
    public function getId();
    public function getAmount(): int;
    public function getUser(): array;
    public function getEmail(): ?string;
    public function getPhone(): ?string;
    public function getStatus(): string;

    public function options(?string $keys = null): mixed;
}
