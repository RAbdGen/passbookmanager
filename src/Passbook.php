<?php

namespace App;

use App\Exception\InvalidAmountException;
use App\Exception\InvalidTransactionDateException;
use App\Interface\FortnightInterface;
use App\Interface\PassbookInterface;
use App\Interface\TransactionInterface;
use DateTimeInterface;
use Ds\Vector;

class Passbook implements PassbookInterface
{
    private Vector $transactions;

    public function __construct(TransactionInterface ...$transactions)
    {
        $this->transactions = new Vector();
        foreach ($transactions as $transaction) {
            $this->addTransactions($transaction);
        }
    }

    public function addTransactions(TransactionInterface ...$transactions): void
    {
        foreach ($transactions as $transaction) {
            if (!$this->transactions->isEmpty()) {
                $lastTransactionDate = $this->transactions->last()->getCreatedOn();
                if ($transaction->getCreatedOn() < $lastTransactionDate) {
                    throw new InvalidTransactionDateException('Transactions must be added in chronological order');
                }
            }

            if ($transaction->getAmount() < 0 && $this->getBalance() + $transaction->getAmount() < 0) {
                throw new InvalidAmountException('The passbook cannot have a negative balance!');
            }

            $this->transactions->push($transaction);
        }
    }

    public function getTransactions(): Vector
    {
        return $this->transactions;
    }

    public function getBalance(): float
    {
        $balance = 0.0;
        foreach ($this->transactions as $transaction) {
            $balance += $transaction->getAmount();
        }
        return $balance;
    }

    public function getBalanceOn(DateTimeInterface $dateBoundary): float
    {
        $balance = 0.0;
        foreach ($this->transactions as $transaction) {
            if ($transaction->getCreatedOn() <= $dateBoundary) {
                $balance += $transaction->getAmount();
            }
        }
        return $balance;
    }

    public function getAmountForFortnightInterestCalculation(FortnightInterface $fortnight): float
    {
        $amount = 0.0;
        $startDate = $fortnight->getStartDate();
        $endDate = $fortnight->getEndDate();

        foreach ($this->transactions as $transaction) {
            $transactionDate = $transaction->getCreatedOn();
            $amountTransaction = $transaction->getAmount();

            if ($transactionDate >= $startDate && $transactionDate < $endDate) {
                $amount += $amountTransaction;
            }
        }

        if ($this->getBalanceOn($startDate) > 0) {
            $amount += $this->getBalanceOn($startDate);
        }

        return $amount;
    }




    public function getInterestForFortnight(FortnightInterface $fortnight): float
    {
        $amount = $this->getAmountForFortnightInterestCalculation($fortnight);
        $interestRate = $fortnight->getInterestRate();

        if ($amount <= 0) {
            return 0.0;
        }

        return $amount * $interestRate / 24;
    }



    public function getInterestForYear(int $year): float
    {
        $totalInterest = 0.0;

        foreach ($this->transactions as $transaction) {
            $transactionDate = $transaction->getCreatedOn();
            $amount = $transaction->getAmount();

            if ($transactionDate->format('Y') == $year) {
                $fortnight = new Fortnight($transactionDate->format('Y-m-d'));
                $interestRate = $fortnight->getInterestRate();

                if ($amount > 0) {
                    $totalInterest += $amount * $interestRate / 24;
                }
            }
        }

        return $totalInterest;
    }
}