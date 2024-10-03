<?php

namespace App\Interface;

use App\Interface\TransactionInterface;
use DateTimeInterface;
use Ds\Vector;

interface PassbookInterface
{
    public function addTransactions(TransactionInterface ...$transaction);

    public function getTransactions(): Vector;

    public function getBalance(): float;

    public function getBalanceOn(DateTimeInterface $dateBoundary): float;

    public function getAmountForFortnightInterestCalculation(FortnightInterface $fortnight): float;

    public function getInterestForYear(int $year): float;

}