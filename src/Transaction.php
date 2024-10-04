<?php

namespace App;

use App\Exception\InvalidAmountException;
use App\Interface\TransactionInterface;
use DateTimeInterface;

class Transaction implements TransactionInterface {
    private float $amount;
    private DateTimeInterface $createdOn;

    public function __construct(float $amount, DateTimeInterface $creationDate) {
        if ($amount < 10 && $amount > -10) {
            throw new InvalidAmountException('Invalid transaction amount. Deposit or withdrawal must be above 10 euros!');
        }

        $this->amount = $amount;
        $this->createdOn = $creationDate;
    }

    public function getCreatedOn(): DateTimeInterface {
        return $this->createdOn;
    }

    public function getAmount(): float {
        return $this->amount;
    }

    public function getType(): string {
        return $this->amount > 0 ? 'DEPOSIT' : 'WITHDRAW';
    }
}
