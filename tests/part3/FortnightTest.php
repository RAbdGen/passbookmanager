<?php

namespace App;

use App\Exception\InvalidFortnightException;
use App\Interface\FortnightInterface;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

class FortnightTest extends TestCase
{
    /**
     * @test
     *
     * Une quinzaine ne peut être instanciée qu’avec une date représentant le premier ou le 16 du mois.
     * Si ce n’est pas le cas, une exception InvalidFortnightException doit être levée.
     */
    public function shouldResturnInvalidFortnightExceptionOnBadDate()
    {
        $this->expectException(InvalidFortnightException::class);
        $fortnight = new Fortnight("2022-07-18");
    }

    /**
     * @test
     *
     * Une quinzaine peut être instanciée avec une date représentant le premier ou le 16 du mois.
     */
    public function shouldInstantiateFortnight()
    {
        $firstFortnightOfJuly = new Fortnight("2022-07-01");
        $secondFortnightOfJuly = new Fortnight("2022-07-16");

        self::assertThat($firstFortnightOfJuly, self::isInstanceOf(FortnightInterface::class));
        self::assertThat($secondFortnightOfJuly, self::isInstanceOf(FortnightInterface::class));
    }

    /**
     * @test
     *
     * L’instantiation d’une quinzaine est faite avec une chaine de caractère représentant une date au format iso
     * YYYY-MM-DD mais cette dernière doit être convertie en DateTimeInterface pour être manipulée et formatée plus
     * facilement.
     */
    public function shouldConvertDateInStringToDateTimeInterface()
    {
        $firstFortnightOfJuly = new Fortnight("2022-07-01");
        $secondFortnightOfJuly = new Fortnight("2022-07-16");

        self::assertThat($firstFortnightOfJuly->getStartDate(), self::isInstanceOf(DateTimeInterface::class));
        self::assertThat($firstFortnightOfJuly->getStartDate(), self::equalTo(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-07-01 00:00:00')));
        self::assertThat($secondFortnightOfJuly->getStartDate(), self::isInstanceOf(DateTimeInterface::class));
        self::assertThat($secondFortnightOfJuly->getStartDate(), self::equalTo(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-07-16 00:00:00')));
    }

    /**
     * @test
     *
     * La méthode getEndDate doit retourner la date de la fin de la quinzaine.
     * Si la quinzaine débute le 01 juillet 2022 (à minuit) alors sa date de fin sera le 15 juillet 2022 à 23h 59 minutes et 59 secondes.
     * Si la quinzaine débute le 16 juillet 2022 (à minuit) alors sa date de fin sera le 31 juillet 2022 à 23h 59 minutes et 59 secondes.
     */
    public function shouldConvertEndDateForFortnight()
    {
        $firstFortnightOfJuly = new Fortnight("2022-07-01");
        $secondFortnightOfJuly = new Fortnight("2022-07-16");

        self::assertThat($firstFortnightOfJuly->getEndDate(), self::isInstanceOf(DateTimeInterface::class));
        self::assertThat($firstFortnightOfJuly->getEndDate(), self::equalTo(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-07-15 23:59:59')));
        self::assertThat($secondFortnightOfJuly->getEndDate(), self::isInstanceOf(DateTimeInterface::class));
        self::assertThat($secondFortnightOfJuly->getEndDate(), self::equalTo(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-07-31 23:59:59')));
    }

    /**
     * @test
     *
     * Les quinzaines avant aout 2022 ont un taux d’intéret de 1%.
     */
    public function shouldReturnOnePercentInterestRate()
    {
        $firstFortnight = new Fortnight("2022-01-01");
        $secondFortnight = new Fortnight("2022-05-16");
        $thirdFortnight = new Fortnight("2022-07-01");

        self::assertThat($firstFortnight->getInterestRate(), self::identicalTo(0.01));
        self::assertThat($secondFortnight->getInterestRate(), self::identicalTo(0.01));
        self::assertThat($thirdFortnight->getInterestRate(), self::identicalTo(0.01));
    }

    /**
     * @test
     *
     * Les quinzaines après aout 2022 ont un taux d’intéret de 2%.
     */
    public function shouldReturnTwoPercentsInterrestRate()
    {
        $firstFortnight = new Fortnight("2022-08-01");
        $secondFortnight = new Fortnight("2022-08-16");
        $thirdFortnight = new Fortnight("2022-11-16");

        self::assertThat($firstFortnight->getInterestRate(), self::identicalTo(0.02));
        self::assertThat($secondFortnight->getInterestRate(), self::identicalTo(0.02));
        self::assertThat($thirdFortnight->getInterestRate(), self::identicalTo(0.02));
    }

    /**
     * @test
     *
     * La méthode createFortnightForYear doit permettre de retourner un tableau composé des 24 quinzaines composant
     * l’année indiquée.
     */
    public function shoudReturn24FortnightOf2022()
    {
        $fortnightFor2022 = Fortnight::createFortnightForYear(2022);

        self::assertThat($fortnightFor2022, self::isType('array'));
        self::assertThat($fortnightFor2022, self::countOf(24));
        self::assertThat($fortnightFor2022, self::containsOnlyInstancesOf(FortnightInterface::class));
        self::assertThat($fortnightFor2022[0]->getStartDate()->format("Y-m-d"), self::identicalTo("2022-01-01"));
        self::assertThat($fortnightFor2022[23]->getStartDate()->format("Y-m-d"), self::identicalTo("2022-12-16"));
    }
}
