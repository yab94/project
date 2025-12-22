<?php

declare(strict_types=1);

namespace App\Banking\Application;

use App\Banking\Domain\AggregateRoot\BankAccount;
use App\Banking\Domain\Entity\BankTransaction;
use App\Banking\Domain\Repository\BankAccountRepositoryInterface;
use App\Banking\Domain\Repository\BankTransactionRepositoryInterface;
use App\Banking\Domain\ValueObject\BankAccountId;
use App\Banking\Domain\ValueObject\BankTransactionId;
use App\Banking\Domain\ValueObject\IBAN;
use App\Banking\Domain\ValueObject\TransactionType;
use App\Billing\Domain\ValueObject\Amount;
use App\Billing\Domain\ValueObject\InvoiceId;

final class BankAccountService
{
    private BankAccountRepositoryInterface $bankAccountRepository;
    private BankTransactionRepositoryInterface $bankTransactionRepository;

    public function __construct(
        BankAccountRepositoryInterface $bankAccountRepository,
        BankTransactionRepositoryInterface $bankTransactionRepository
    ) {
        $this->bankAccountRepository = $bankAccountRepository;
        $this->bankTransactionRepository = $bankTransactionRepository;
    }

    public function createBankAccount(string $name, string $iban, ?string $bic = null): BankAccount
    {
        $account = BankAccount::create($name, $iban, $bic);
        $this->bankAccountRepository->save($account);
        return $account;
    }

    public function addTransaction(
        string $bankAccountId,
        \DateTimeImmutable $date,
        string $type,
        float $amount,
        string $label
    ): BankTransaction {
        $account = $this->bankAccountRepository->findById(new BankAccountId($bankAccountId));
        if (!$account) {
            throw new \DomainException("Bank account not found: {$bankAccountId}");
        }

        $transactionType = TransactionType::from($type);
        $amountObj = new Amount($amount);

        $transaction = BankTransaction::create(
            new BankAccountId($bankAccountId),
            $date,
            $transactionType,
            $amountObj,
            $label
        );

        if ($transactionType === TransactionType::CREDIT) {
            $account->credit($amountObj);
        } else {
            $account->debit($amountObj);
        }

        $this->bankTransactionRepository->save($transaction);
        $this->bankAccountRepository->save($account);

        return $transaction;
    }

    public function linkTransactionToInvoice(string $transactionId, string $invoiceId): void
    {
        $transaction = $this->bankTransactionRepository->findById(
            new BankTransactionId($transactionId)
        );
        if (!$transaction) {
            throw new \DomainException("Transaction not found: {$transactionId}");
        }

        $transaction->linkToInvoice(new InvoiceId($invoiceId));
        $this->bankTransactionRepository->save($transaction);
    }

    public function reconcileTransaction(string $transactionId): void
    {
        $transaction = $this->bankTransactionRepository->findById(
            new BankTransactionId($transactionId)
        );
        if (!$transaction) {
            throw new \DomainException("Transaction not found: {$transactionId}");
        }

        $transaction->reconcile();
        $this->bankTransactionRepository->save($transaction);
    }

    public function findBankAccountById(string $id): ?BankAccount
    {
        return $this->bankAccountRepository->findById(new BankAccountId($id));
    }

    /** @return BankAccount[] */
    public function findAllBankAccounts(): array
    {
        return $this->bankAccountRepository->findAll();
    }

    /** @return BankTransaction[] */
    public function findTransactionsByBankAccountId(string $bankAccountId): array
    {
        return $this->bankTransactionRepository->findByBankAccountId(
            new BankAccountId($bankAccountId)
        );
    }
}
