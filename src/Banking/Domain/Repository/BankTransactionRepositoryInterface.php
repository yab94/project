<?php

declare(strict_types=1);

namespace App\Banking\Domain\Repository;

use App\Banking\Domain\Entity\BankTransaction;
use App\Banking\Domain\ValueObject\BankAccountId;
use App\Banking\Domain\ValueObject\BankTransactionId;
use App\Billing\Domain\ValueObject\InvoiceId;
use App\Core\Domain\RepositoryInterface;

interface BankTransactionRepositoryInterface extends RepositoryInterface
{
    public function save(BankTransaction $transaction): void;
    
    public function findById(BankTransactionId $id): ?BankTransaction;
    
    /** @return BankTransaction[] */
    public function findAll(): array;
    
    /** @return BankTransaction[] */
    public function findByBankAccountId(BankAccountId $bankAccountId): array;
    
    /** @return BankTransaction[] */
    public function findByInvoiceId(InvoiceId $invoiceId): array;
    
    public function delete(BankTransactionId $id): void;
}
