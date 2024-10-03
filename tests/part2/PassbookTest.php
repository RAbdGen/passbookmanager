<?php

namespace App;

use App\Exception\InvalidAmountException;
use App\Exception\InvalidTransactionDateException;
use DateTimeImmutable;
use Ds\Vector;
use App\Interface\PassbookInterface;
use PHPUnit\Framework\TestCase;

class PassbookTest extends TestCase
{
    /**
     * @test
     *
     * La classe Passbook doit implémenter l’interface PassbookInterface
     */
    public function shouldBePassbookInterface(): void
    {
        $passbook = new Passbook();

        self::assertThat($passbook, self::isInstanceOf(PassbookInterface::class));
    }

    /**
     * @test
     *
     * Il doit être possible d’instancier un Passbook en passant une première transaction.
     * La méthode getTransactions renvoie toutes les transactions liées au Passbook dans un objet Ds\Vector.
     *
     */
    public function shouldAddOneTransactionByConstructor(): void {
        $creationDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-01-09 15:10:22');
        $initialTransaction = new Transaction(1280.00, $creationDate);

        $passbook = new Passbook($initialTransaction);

        self::assertThat($passbook->getTransactions(), self::isInstanceOf(Vector::class));
        self::assertThat($passbook->getTransactions(), self::countOf(1));
        self::assertThat($passbook->getTransactions(), self::containsOnlyInstancesOf(Transaction::class));
    }

    /**
     * @test
     *
     * Il doit être possible d’ajouter autant de transaction que l’on souhaite avec la méthode addTransactions.
     * Voir ce que l’on appèle « Argument Unpackin » : https://wiki.php.net/rfc/argument_unpacking
     * ou splat operator
     */
    public function shouldReturnVectorOfTransaction(): void {
        $creationDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-01-09 15:10:22');
        $firstTransaction = new Transaction(1280.00, $creationDate);
        $secondTransaction = new Transaction(720.00, $creationDate);
        $thirdTransaction = new Transaction(920.00, $creationDate);

        $passbook = new Passbook($firstTransaction);
        $passbook->addTransactions($secondTransaction, $thirdTransaction);

        self::assertThat($passbook->getTransactions(), self::isInstanceOf(Vector::class));
        self::assertThat($passbook->getTransactions(), self::countOf(3));
        self::assertThat($passbook->getTransactions(), self::containsOnlyInstancesOf(Transaction::class));
    }

    /**
     * @test
     *
     * Un compte sans transaction devra retourner un sold de 0.0 € avec la méthode getBalance.
     */
    public function shouldReturn0AsTotalTotalAmountWhenNotTransactionRegistered(): void
    {
        $passbook = new Passbook();

        self::assertThat($passbook->getBalance(), self::identicalTo(0.0));
    }

    /**
     * @test
     *
     * Un compte initialisé avec une transaction de 1280 € devra retourner un solde de 1280 €.
     */
    public function shouldReturnBaseAmountAsTotalAmountWhenNotTransactionRegistered(): void
    {
        $creationDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-01-09 15:10:22');
        $transaction = new Transaction(1280.00, $creationDate);
        $passbook = new Passbook($transaction);

        self::assertThat($passbook->getBalance(), self::identicalTo(1280.0));
    }

    /**
     * @test
     *
     * Un compte avec deux dépôts de 1280 et 720 € devra retourner un solde de 2000 €.
     */
    public function shouldReturn2000AsTotalAmountWith720TransactionAmount(): void
    {
        $creationDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-01-09 15:10:22');
        $firstTransaction = new Transaction(1280.00, $creationDate);
        $secondTransaction = new Transaction(720.00, $creationDate);
        $passbook = new Passbook($firstTransaction);
        $passbook->addTransactions($secondTransaction);

        self::assertThat($passbook->getBalance(), self::identicalTo(2000.0));
    }


    /**
     * @test
     *
     * Un compte avec un dépôt de 1280 € et un retrait de 720 € devra retourner un solde de 1000 €.
     */
    public function shouldReturn1000AsTotalAmountWithNegative280TransactionAmount(): void
    {
        $creationDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-01-09 15:10:22');
        $firstTransaction = new Transaction(1280.00, $creationDate);
        $secondTransaction = new Transaction(-280.00, $creationDate);
        $passbook = new Passbook($firstTransaction);
        $passbook->addTransactions($secondTransaction);

        self::assertThat($passbook->getBalance(), self::identicalTo(1000.0));
    }


    /**
     * @test
     *
     * Il n’est pas possible de retirer plus d‘argent qu‘il y a sur le compte.
     * Le compte ne peut pas avoir un solde négatif.
     * Si on ajoute une transaction trop importante qui mettrait le solde en négatif, une exception
     * InvalidAmountException sera envoyée.
     */
    public function shouldThrowInvalidAmountExceptionWhenWithdrawalIsGreaterThanTheAccountBalance(): void
    {
        $creationDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-01-09 15:10:22');
        $passbook = new Passbook(new Transaction(1280.00, $creationDate));
        $secondTransaction = new Transaction(-2200.00, $creationDate);

        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('The passbook cannot have a negative balance!');
        $passbook->addTransactions($secondTransaction);
    }

    /**
     * @test
     *
     * Les transactions doivent être ajoutées dans l’ordre chronologique.
     * Si on tente d’ajouter une transaction avec une date inférieure à celle de la précédente transaction,
     * une exception InvalidTransactionDateException devra être levée.
     */
    public function shouldThrowInvalidTransactionDateExceptionWhenAddingTransactionOlderThanTheLast(): void
    {
        $transactionOnFebruary = new Transaction(
            1000.00,
            DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-02-01 15:10:22')
        );
        $transactionOnMarch = new Transaction(
            1000.00,
            DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-03-01 15:10:22')
        );
        $passbook = new Passbook();
        $passbook->addTransactions($transactionOnMarch);

        $this->expectException(InvalidTransactionDateException::class);
        $this->expectExceptionMessage('Transactions must be added in chronological order');
        $passbook->addTransactions($transactionOnFebruary);
    }

    /**
     * @test
     *
     * Les transactions doivent être ajoutées dans l’ordre chronologique.
     * La vérification de la date doit prendre en compte les heures des transactions.
     */
    public function shouldThrowInvalidTransactionDateExceptionWhenAddingTransactionOlderThanTheLast2(): void
    {
        $transactionOnMarchAt15 = new Transaction(
            1000.00,
            DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-03-01 15:10:22')
        );
        $transactionOnMarchAt14 = new Transaction(
            1000.00,
            DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-03-01 14:10:22')
        );
        $passbook = new Passbook();
        $passbook->addTransactions($transactionOnMarchAt15);

        $this->expectException(InvalidTransactionDateException::class);
        $this->expectExceptionMessage('Transactions must be added in chronological order');
        $passbook->addTransactions($transactionOnMarchAt14);
    }

    /**
     * @test
     *
     * La méthode getBalanceOn doit nous permettre de retrouver le solde du compte à une certaine date.
     * La méthode prendra une date en paramètre comme l’indique l’interface.
     */
    public function shouldReturn220EuroAccountBalanceOnFirstMarch(): void
    {
        $creationDateOnJanuary = DateTimeImmutable::createFromFormat('Y-m-d', '2022-01-09');
        $creationDateOnFebruary = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-02-28 08:20:28');
        $creationDateOnMarch = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-03-08 01:19:49');
        $passbook = new Passbook();
        $passbook->addTransactions(
            new Transaction(500.00, $creationDateOnJanuary),
            new Transaction(-280.00, $creationDateOnFebruary),
            new Transaction(1300.00, $creationDateOnMarch),
        );

        $amountOnFirstMarch = $passbook->getBalanceOn(
            DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-03-01 00:00:00')
        );

        self::assertThat($amountOnFirstMarch, self::identicalTo(220.0));
    }
}
