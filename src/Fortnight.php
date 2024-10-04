<?php

namespace App;

use App\Exception\InvalidFortnightException;
use App\Interface\FortnightInterface;
use DateTimeImmutable;
use DateTimeInterface;

class Fortnight implements FortnightInterface
{
    private DateTimeImmutable $startDate;

    public function __construct(string $date)
    {
        $startDate = new DateTimeImmutable($date);
        $day = (int) $startDate->format('d');

        if ($day !== 1 && $day !== 16) {
            throw new InvalidFortnightException("Invalid start date for a fortnight.");
        }

        $this->startDate = $startDate;
    }

    public function getStartDate(): DateTimeInterface
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeInterface
    {
        $day = (int) $this->startDate->format('d');

        if ($day === 1) {
            return $this->startDate->setDate(
                (int) $this->startDate->format('Y'),
                (int) $this->startDate->format('m'),
                15
            )->setTime(23, 59, 59);
        }

        return $this->startDate->modify('last day of this month')->setTime(23, 59, 59);
    }

    public function getInterestRate(): float
    {
        $rateChangeDate = new DateTimeImmutable('2022-08-01');

        if ($this->startDate < $rateChangeDate) {
            return 0.01;
        }

        return 0.02;
    }

    public static function createFortnightForYear(int $year): array
    {
        $fortnights = [];

        for ($month = 1; $month <= 12; $month++) {
            $firstFortnight = new self("$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01");
            $secondFortnight = new self("$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-16");

            $fortnights[] = $firstFortnight;
            $fortnights[] = $secondFortnight;
        }

        return $fortnights;
    }
}
