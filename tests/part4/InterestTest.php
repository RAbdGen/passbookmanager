<?php

namespace App;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class InterestTest extends TestCase
{

    /**
     * @test
     *
     * Le montant sur lequel seront calculés les intérets de la quinzaine du 16 novembre seront de 1500 €, car la retrait
     * est compris dans la quinzaine précédente et la dépôt du 13 est compris dans la quinzaine du 16.
     * Le dépôt de 90 € ne sera pris en compte que dans la quinzaine du 1 décembre.
     */
    public function shouldReturnAmountForFortnightInterestCalculation() {
        $passbook = new Passbook(new Transaction(1000, DateTimeImmutable::createFromFormat('Y-m-d', '2021-12-01')));
        $passbook->addTransactions(
            new Transaction(-500, DateTimeImmutable::createFromFormat('Y-m-d', '2022-11-07')),
            new Transaction(1000, DateTimeImmutable::createFromFormat('Y-m-d', '2022-11-13')),
            new Transaction(90, DateTimeImmutable::createFromFormat('Y-m-d', '2022-11-19')),
        );

        $amountForInterestCalculation1 = $passbook->getAmountForFortnightInterestCalculation(new Fortnight("2022-11-16"));
        $amountForInterestCalculation2 = $passbook->getAmountForFortnightInterestCalculation(new Fortnight("2022-12-01"));

        self::assertThat($amountForInterestCalculation1, self::identicalTo(1500.0));
        self::assertThat($amountForInterestCalculation2, self::identicalTo(1590.0));
    }

    /**
     * @test
     *
     * Pour John, le montant sur lequel seront calculés les intérets au 1 novembre seront de 500 €, car son retrait
     * est compris dans la quinzaine en cours (voir règle métier de l’énoncé).
     */
    public function shouldReturn500ForFortnightOfFirstNovember() {
        $passbook = new Passbook(new Transaction(1000, DateTimeImmutable::createFromFormat('Y-m-d', '2021-12-01')));
        $passbook->addTransactions(
            new Transaction(-500, DateTimeImmutable::createFromFormat('Y-m-d', '2022-11-07')),
            new Transaction(1000, DateTimeImmutable::createFromFormat('Y-m-d', '2022-11-13')),
        );

        $balanceForInterestCalculation = $passbook->getAmountForFortnightInterestCalculation(new Fortnight("2022-11-01"));

        self::assertThat($balanceForInterestCalculation, self::identicalTo(500.0));
    }

    /**
     * @test
     *
     * Pour John, les intérets de la quinzaine du 1 novembre sont de 0.4166666666666667 €
     * Voir la règle de calcul métier : intéret de la quinzaine = montant du compte au moment de la quinzaine * pourcentage d’intéret / 24
     * Dans ce cas-ci : intéret de la quinzaine = 500 * 0.02 / 24 = 0,4166666666666667
     */
    public function shouldReturnFortnightInterestForFirstNovember() {
        $passbook = new Passbook(new Transaction(1000, DateTimeImmutable::createFromFormat('Y-m-d', '2021-12-01')));
        $passbook->addTransactions(
            new Transaction(-500, DateTimeImmutable::createFromFormat('Y-m-d', '2022-11-07')),
            new Transaction(1000, DateTimeImmutable::createFromFormat('Y-m-d', '2022-11-13')),
        );

        $balanceForInterestCalculation = $passbook->getInterestForFortnight(new Fortnight("2022-11-01"));

        self::assertThat($balanceForInterestCalculation, self::identicalTo(0.4166666666666667));
    }

    /**
     * @test
     *
     * Pour John, les intérets de la quinzaine du 1 décembre sont de 1.25 €
     * Voir la règle de calcul métier : intéret de la quinzaine = montant du compte au moment de la quinzaine * pourcentage d’intéret / 24
     */
    public function shouldReturnFortnightInterestForFirstDecember() {
        $passbook = new Passbook(new Transaction(1000, DateTimeImmutable::createFromFormat('Y-m-d', '2021-12-01')));
        $passbook->addTransactions(
            new Transaction(-500, DateTimeImmutable::createFromFormat('Y-m-d', '2022-11-07')),
            new Transaction(1000, DateTimeImmutable::createFromFormat('Y-m-d', '2022-11-13')),
        );

        $balanceForInterestCalculation = $passbook->getInterestForFortnight(new Fortnight("2022-12-01"));

        self::assertThat($balanceForInterestCalculation, self::identicalTo(1.25));
    }

    /**
     * @test
     *
     * Reprise de l’exemple de John de l’énoncé
     */
    public function shouldReturn1458centOfInterestForJohn() {
        $passbook = new Passbook(new Transaction(1000, DateTimeImmutable::createFromFormat('Y-m-d', '2021-12-01')));
        $passbook->addTransactions(
            new Transaction(-500, DateTimeImmutable::createFromFormat('Y-m-d', '2022-11-07')),
            new Transaction(1000, DateTimeImmutable::createFromFormat('Y-m-d', '2022-11-13')),
        );

        $interest = $passbook->getInterestForYear(2022);

        self::assertThat($interest, self::identicalTo(15.000000000000002));
    }

    /**
     * @test
     *
     * Reprise de l’exemple de Jane de l’énoncé
     */
    public function shouldReturn2625centOfInterestForJane() {
        $passbook = new Passbook(new Transaction(1000, DateTimeImmutable::createFromFormat('Y-m-d', '2021-12-01')));
        $passbook->addTransactions(
            new Transaction(1000, DateTimeImmutable::createFromFormat('Y-m-d', '2022-01-23')),
            new Transaction(-500, DateTimeImmutable::createFromFormat('Y-m-d', '2022-12-24')),
        );

        $interest = $passbook->getInterestForYear(2022);

        self::assertThat($interest, self::identicalTo(27.08333333333334));
    }

    /**
     * @test
     *
     * Reprise de l’exemple de Jane de l’énoncé
     */
    public function shouldReturn2625centOfInterestForJanXe() {
        $passbook = new Passbook(new Transaction(1000, DateTimeImmutable::createFromFormat('Y-m-d', '2021-12-01')));
        $passbook->addTransactions(
            new Transaction(1000, DateTimeImmutable::createFromFormat('Y-m-d', '2022-01-23')),
            new Transaction(-500, DateTimeImmutable::createFromFormat('Y-m-d', '2022-12-24')),
        );

        $interest = $passbook->getInterestForYear(2023);

        self::assertThat($interest, self::identicalTo(30.0));
    }
}
