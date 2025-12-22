<?php

declare(strict_types=1);

namespace App\Billing\Infrastructure\Persistence;

use App\Billing\Domain\AggregateRoot\Quote;
use App\Billing\Domain\ValueObject\QuoteLine;
use App\Billing\Domain\Repository\QuoteRepositoryInterface;
use App\Billing\Domain\ValueObject\Amount;
use App\Billing\Domain\ValueObject\QuoteId;
use App\Billing\Domain\ValueObject\QuoteStatus;
use App\CRM\Domain\ValueObject\PersonId;
use App\Core\Infrastructure\Persistence\Database;

final class PDOQuoteRepository implements QuoteRepositoryInterface
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function save(Quote $quote): void
    {
        $this->pdo->beginTransaction();
        try {
            // Save quote
            $stmt = $this->pdo->prepare('
                INSERT INTO quotes (id, client_id, number, created_at, valid_until, status, notes)
                VALUES (:id, :client_id, :number, :created_at, :valid_until, :status, :notes)
                ON DUPLICATE KEY UPDATE
                    client_id = VALUES(client_id),
                    number = VALUES(number),
                    created_at = VALUES(created_at),
                    valid_until = VALUES(valid_until),
                    status = VALUES(status),
                    notes = VALUES(notes)
            ');

            $stmt->execute([
                'id' => $quote->id()->value(),
                'client_id' => $quote->clientId()->value(),
                'number' => $quote->number(),
                'created_at' => $quote->createdAt()->format('Y-m-d H:i:s'),
                'valid_until' => $quote->validUntil()->format('Y-m-d H:i:s'),
                'status' => $quote->status()->value,
                'notes' => $quote->notes(),
            ]);

            // Delete existing lines and re-insert
            $stmt = $this->pdo->prepare('DELETE FROM quote_lines WHERE quote_id = :quote_id');
            $stmt->execute(['quote_id' => $quote->id()->value()]);

            // Insert lines
            foreach ($quote->lines() as $index => $line) {
                $stmt = $this->pdo->prepare('
                    INSERT INTO quote_lines (quote_id, line_number, description, quantity, unit_price, details)
                    VALUES (:quote_id, :line_number, :description, :quantity, :unit_price, :details)
                ');
                $stmt->execute([
                    'quote_id' => $quote->id()->value(),
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

    public function findById(QuoteId $id): ?Quote
    {
        $stmt = $this->pdo->prepare('SELECT * FROM quotes WHERE id = :id');
        $stmt->execute(['id' => $id->value()]);
        
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    /** @return Quote[] */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM quotes ORDER BY created_at DESC');
        $rows = $stmt->fetchAll();

        return array_map([$this, 'hydrate'], $rows);
    }

    /** @return Quote[] */
    public function findByClientId(PersonId $clientId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM quotes WHERE client_id = :client_id ORDER BY created_at DESC');
        $stmt->execute(['client_id' => $clientId->value()]);
        $rows = $stmt->fetchAll();

        return array_map([$this, 'hydrate'], $rows);
    }

    public function delete(QuoteId $id): void
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('DELETE FROM quote_lines WHERE quote_id = :id');
            $stmt->execute(['id' => $id->value()]);

            $stmt = $this->pdo->prepare('DELETE FROM quotes WHERE id = :id');
            $stmt->execute(['id' => $id->value()]);

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function hydrate(array $row): Quote
    {
        $quote = new Quote(
            new QuoteId($row['id']),
            new PersonId($row['client_id']),
            $row['number'],
            new \DateTimeImmutable($row['created_at']),
            new \DateTimeImmutable($row['valid_until']),
            QuoteStatus::from($row['status']),
            $row['notes']
        );

        // Load lines
        $stmt = $this->pdo->prepare('
            SELECT * FROM quote_lines WHERE quote_id = :quote_id ORDER BY line_number
        ');
        $stmt->execute(['quote_id' => $row['id']]);
        $lines = $stmt->fetchAll();

        // Use reflection to set lines directly (bypass business rules during hydration)
        $reflection = new \ReflectionClass($quote);
        $linesProperty = $reflection->getProperty('lines');
        $linesProperty->setAccessible(true);
        
        $quoteLines = [];
        foreach ($lines as $lineRow) {
            $quoteLines[] = new QuoteLine(
                $lineRow['description'],
                (int) $lineRow['quantity'],
                new Amount((float) $lineRow['unit_price']),
                $lineRow['details']
            );
        }
        $linesProperty->setValue($quote, $quoteLines);

        return $quote;
    }
}
