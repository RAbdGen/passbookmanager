<?php

namespace App;

use App\Exception\InvalidAmountException;
use App\Interface\TransactionInterface;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    /**
     * @test
     *
     * On souhaite vérifier que la classe Transaction implémente bien l’interface TransactionInterface
     */
    public function transactionMustImplementTransactionInterface(): void
    {
        $creationDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-01-09 15:10:22');
        $transaction = new Transaction(150.00, $creationDate);

        self::assertThat($transaction, self::isInstanceOf(TransactionInterface::class));
    }

    /**
     * @test
     *
     * La méthode getCreatedOn doit retourner l’objet représentant la date de transaction.
     */
    public function shouldReturnCreationDate(): void
    {
        $creationDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-01-09 15:10:22');
        $transaction = new Transaction(100.00, $creationDate);

        self::assertThat($transaction->getCreatedOn(), self::isInstanceOf(DateTimeInterface::class));
    }

    /**
     * @test
     *
     * Seuls les mouvements (dépôt ou retrait) supérieurs à 10 € sont autorisés.
     * Un dépôt de 7,50 € devra donc provoquer une exception.
     */
    public function shouldThrowInvalidAmountExceptionForDepositUnderTenEuro(): void
    {
        $creationDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-01-09 15:10:22');

        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Invalid transaction amount. Deposit or withdrawal must be above 10 euros!');
        $transaction = new Transaction(7.50, $creationDate);
    }

    /**
     * @test
     *
     * Seuls les mouvements (dépôt ou retrait) supérieurs à 10 € sont autorisés.
     * Un retrait de 3,00 € devra donc provoquer une exception.
     */
    public function shouldThrowInvalidAmountExceptionForWithdrawalUnderTenEuro(): void
    {
        $creationDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-01-09 15:10:22');

        $this->expectException(InvalidAmountException::class);
        $this->expectExceptionMessage('Invalid transaction amount. Deposit or withdrawal must be above 10 euros!');
        $transaction = new Transaction(-3.00, $creationDate);
    }

    /**
     * @test
     *
     * La méthode getAmount devra retourner le montant de la transaction
     */
    public function shouldReturnDefinedAmount(): void
    {
        $creationDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-01-09 15:10:22');
        $transactionWithPositiveAmount = new Transaction(300.00, $creationDate);
        $transactionWithNegativeAmoubt = new Transaction(-199.00, $creationDate);

        self::assertThat($transactionWithPositiveAmount->getAmount(), self::identicalTo(300.00));
        self::assertThat($transactionWithNegativeAmoubt->getAmount(), self::identicalTo(-199.00));
    }

    /**
     * @test
     *
     * Si le montant de la transaction est positif, la transaction sera considérée comme un dépôt
     * et la méthode getType devra retourner la chaine "DEPOSIT"
     */
    public function shouldReturnDepositTypeIfAmountIsPositive(): void
    {
        $creationDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-01-09 15:10:22');
        $transactionWithPositiveAmount = new Transaction(300.00, $creationDate);

        self::assertThat($transactionWithPositiveAmount->getType(), self::identicalTo('DEPOSIT'));
    }

    /**
     * @test
     *
     * Si le montant de la transaction est négatif, la transaction sera considérée comme un retrait d’argent
     * et la méthode getType devra retourner la chaine "WITHDRAW"
     */
    public function shouldReturnWithdrawTypeIfAmountIsNegative(): void
    {
        $creationDate = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-01-09 15:10:22');
        $transactionWithNegativeAmoubt = new Transaction(-199.00, $creationDate);

        self::assertThat($transactionWithNegativeAmoubt->getType(), self::identicalTo('WITHDRAW'));
    }
}
