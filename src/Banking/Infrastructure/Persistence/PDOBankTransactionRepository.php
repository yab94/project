<?php

declare(strict_types=1);

namespace App\Banking\Infrastructure\Persistence;

use App\Banking\Domain\Entity\BankTransaction;
use App\Banking\Domain\Repository\BankTransactionRepositoryInterface;
use App\Banking\Domain\ValueObject\BankAccountId;
use App\Banking\Domain\ValueObject\BankTransactionId;
use App\Banking\Domain\ValueObject\TransactionType;
use App\Billing\Domain\ValueObject\Amount;
use App\Billing\Domain\ValueObject\InvoiceId;
use App\Core\Infrastructure\Persistence\Database;

final class PDOBankTransactionRepository implements BankTransactionRepositoryInterface
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function save(BankTransaction $transaction): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO bank_transactions (id, bank_account_id, date, type, amount, label, invoice_id, reconciled)
            VALUES (:id, :bank_account_id, :date, :type, :amount, :label, :invoice_id, :reconciled)
            ON DUPLICATE KEY UPDATE
                bank_account_id = VALUES(bank_account_id),
                date = VALUES(date),
                type = VALUES(type),
                amount = VALUES(amount),
                label = VALUES(label),
                invoice_id = VALUES(invoice_id),
                reconciled = VALUES(reconciled)
        ');

        $stmt->execute([
            'id' => $transaction->id()->value(),
            'bank_account_id' => $transaction->bankAccountId()->value(),
            'date' => $transaction->date()->format('Y-m-d H:i:s'),
            'type' => $transaction->type()->value,
            'amount' => $transaction->amount()->value(),
            'label' => $transaction->label(),
            'invoice_id' => $transaction->invoiceId()?->value(),
            'reconciled' => $transaction->isReconciled() ? 1 : 0,
        ]);
    }

    public function findById(BankTransactionId $id): ?BankTransaction
    {
        $stmt = $this->pdo->prepare('SELECT * FROM bank_transactions WHERE id = :id');
        $stmt->execute(['id' => $id->value()]);
        
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    /** @return BankTransaction[] */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM bank_transactions ORDER BY date DESC');
        $rows = $stmt->fetchAll();

        return array_map([$this, 'hydrate'], $rows);
    }

    /** @return BankTransaction[] */
    public function findByBankAccountId(BankAccountId $bankAccountId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM bank_transactions WHERE bank_account_id = :bank_account_id ORDER BY date DESC
        ');
        $stmt->execute(['bank_account_id' => $bankAccountId->value()]);
        $rows = $stmt->fetchAll();

        return array_map([$this, 'hydrate'], $rows);
    }

    /** @return BankTransaction[] */
    public function findByInvoiceId(InvoiceId $invoiceId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM bank_transactions WHERE invoice_id = :invoice_id ORDER BY date DESC
        ');
        $stmt->execute(['invoice_id' => $invoiceId->value()]);
        $rows = $stmt->fetchAll();

        return array_map([$this, 'hydrate'], $rows);
    }

    public function delete(BankTransactionId $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM bank_transactions WHERE id = :id');
        $stmt->execute(['id' => $id->value()]);
    }

    private function hydrate(array $row): BankTransaction
    {
        return new BankTransaction(
            new BankTransactionId($row['id']),
            new BankAccountId($row['bank_account_id']),
            new \DateTimeImmutable($row['date']),
            TransactionType::from($row['type']),
            new Amount((float) $row['amount']),
            $row['label'],
            $row['invoice_id'] ? new InvoiceId($row['invoice_id']) : null,
            (bool) $row['reconciled']
        );
    }
}
