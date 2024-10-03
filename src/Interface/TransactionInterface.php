<?php

namespace App\Interface;

use DateTimeInterface;

interface TransactionInterface
{
    public function getCreatedOn(): DateTimeInterface;

    public function getAmount(): float;

    public function getType(): string;
}