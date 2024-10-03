<?php

namespace App\Interface;

use DateTimeInterface;

interface FortnightInterface
{
    public function getInterestRate(): float;

    public function getStartDate(): DateTimeInterface;

    public static function createFortnightForYear(int $year): array;
}