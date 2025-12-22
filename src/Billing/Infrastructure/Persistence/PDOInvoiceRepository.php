<?php

declare(strict_types=1);

namespace App\Billing\Infrastructure\Persistence;

use App\Billing\Domain\AggregateRoot\Invoice;
use App\Billing\Domain\ValueObject\InvoiceLine;
use App\Billing\Domain\Repository\InvoiceRepositoryInterface;
use App\Billing\Domain\ValueObject\Amount;
use App\Billing\Domain\ValueObject\InvoiceId;
use App\Billing\Domain\ValueObject\InvoiceStatus;
use App\Billing\Domain\ValueObject\QuoteId;
use App\CRM\Domain\ValueObject\PersonId;
use App\Core\Infrastructure\Persistence\Database;

final class PDOInvoiceRepository implements InvoiceRepositoryInterface
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function save(Invoice $invoice): void
    {
        $this->pdo->beginTransaction();
        try {
            // Save invoice
            $stmt = $this->pdo->prepare('
                INSERT INTO invoices (id, client_id, quote_id, number, issued_at, due_date, status, notes, paid_amount)
                VALUES (:id, :client_id, :quote_id, :number, :issued_at, :due_date, :status, :notes, :paid_amount)
                ON DUPLICATE KEY UPDATE
                    client_id = VALUES(client_id),
                    quote_id = VALUES(quote_id),
                    number = VALUES(number),
                    issued_at = VALUES(issued_at),
                    due_date = VALUES(due_date),
                    status = VALUES(status),
                    notes = VALUES(notes),
                    paid_amount = VALUES(paid_amount)
            ');

            $stmt->execute([
                'id' => $invoice->id()->value(),
                'client_id' => $invoice->clientId()->value(),
                'quote_id' => $invoice->quoteId()?->value(),
                'number' => $invoice->number(),
                'issued_at' => $invoice->issuedAt()->format('Y-m-d H:i:s'),
                'due_date' => $invoice->dueDate()->format('Y-m-d H:i:s'),
                'status' => $invoice->status()->value,
                'notes' => $invoice->notes(),
                'paid_amount' => $invoice->paidAmount()?->value(),
            ]);

            // Delete existing lines and re-insert
            $stmt = $this->pdo->prepare('DELETE FROM invoice_lines WHERE invoice_id = :invoice_id');
            $stmt->execute(['invoice_id' => $invoice->id()->value()]);

            // Insert lines
            foreach ($invoice->lines() as $index => $line) {
                $stmt = $this->pdo->prepare('
                    INSERT INTO invoice_lines (invoice_id, line_number, description, quantity, unit_price, details)
                    VALUES (:invoice_id, :line_number, :description, :quantity, :unit_price, :details)
                ');
                $stmt->execute([
                    'invoice_id' => $invoice->id()->value(),
                    'line_number' => $index + 1,
                    'description' => $line->description(),
                    'quantity' => $line->quantity(),
                    'unit_price' => $line->unitPrice()->value(),
                    'details' => $line->details(),
                ]);
            }

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function findById(InvoiceId $id): ?Invoice
    {
        $stmt = $this->pdo->prepare('SELECT * FROM invoices WHERE id = :id');
        $stmt->execute(['id' => $id->value()]);
        
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    /** @return Invoice[] */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM invoices ORDER BY issued_at DESC');
        $rows = $stmt->fetchAll();

        return array_map([$this, 'hydrate'], $rows);
    }

    /** @return Invoice[] */
    public function findByClientId(PersonId $clientId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM invoices WHERE client_id = :client_id ORDER BY issued_at DESC');
        $stmt->execute(['client_id' => $clientId->value()]);
        $rows = $stmt->fetchAll();

        return array_map([$this, 'hydrate'], $rows);
    }

    public function delete(InvoiceId $id): void
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('DELETE FROM invoice_lines WHERE invoice_id = :id');
            $stmt->execute(['id' => $id->value()]);

            $stmt = $this->pdo->prepare('DELETE FROM invoices WHERE id = :id');
            $stmt->execute(['id' => $id->value()]);

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function hydrate(array $row): Invoice
    {
        $invoice = new Invoice(
            new InvoiceId($row['id']),
            new PersonId($row['client_id']),
            $row['quote_id'] ? new QuoteId($row['quote_id']) : null,
            $row['number'],
            new \DateTimeImmutable($row['issued_at']),
            new \DateTimeImmutable($row['due_date']),
            InvoiceStatus::from($row['status']),
            $row['notes']
        );

        // Load lines
        $stmt = $this->pdo->prepare('
            SELECT * FROM invoice_lines WHERE invoice_id = :invoice_id ORDER BY line_number
        ');
        $stmt->execute(['invoice_id' => $row['id']]);
        $lines = $stmt->fetchAll();

        // Use reflection to set lines and paidAmount directly
        $reflection = new \ReflectionClass($invoice);
        
        $linesProperty = $reflection->getProperty('lines');
        $linesProperty->setAccessible(true);
        
        $invoiceLines = [];
        foreach ($lines as $lineRow) {
            $invoiceLines[] = new InvoiceLine(
                $lineRow['description'],
                (int) $lineRow['quantity'],
                new Amount((float) $lineRow['unit_price']),
                $lineRow['details']
            );
        }
        $linesProperty->setValue($invoice, $invoiceLines);

        if ($row['paid_amount'] !== null) {
            $paidAmountProperty = $reflection->getProperty('paidAmount');
            $paidAmountProperty->setAccessible(true);
            $paidAmountProperty->setValue($invoice, new Amount((float) $row['paid_amount']));
        }

        return $invoice;
    }
}
