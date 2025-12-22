<?php

declare(strict_types=1);

namespace App\Billing\Domain\AggregateRoot;

use App\Core\Domain\AggregateRootInterface;
use App\Billing\Domain\ValueObject\Amount;
use App\Billing\Domain\ValueObject\QuoteId;
use App\Billing\Domain\ValueObject\QuoteStatus;
use App\Billing\Domain\ValueObject\QuoteLine;
use App\CRM\Domain\ValueObject\PersonId;

/**
 * Quote Aggregate Root
 * 
 * Manages the Quote aggregate which includes:
 * - QuoteLine value objects (line items with description, quantity, price)
 * 
 * Enforces business rules:
 * - Lines can only be added/removed in DRAFT status
 * - Quote workflow: draft → sent → accepted/rejected/expired
 * - Cannot send empty quotes
 * 
 * All modifications to quote lines must go through Quote to maintain
 * aggregate consistency and enforce workflow rules.
 */
final class Quote implements AggregateRootInterface
{
    private QuoteId $id;
    private PersonId $clientId;
    private string $number;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $validUntil;
    private QuoteStatus $status;
    /** @var QuoteLine[] */
    private array $lines = [];
    private ?string $notes;

    public function __construct(
        QuoteId $id,
        PersonId $clientId,
        string $number,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $validUntil,
        QuoteStatus $status = QuoteStatus::DRAFT,
        ?string $notes = null
    ) {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->number = $number;
        $this->createdAt = $createdAt;
        $this->validUntil = $validUntil;
        $this->status = $status;
        $this->notes = $notes;
    }

    public static function create(
        PersonId $clientId,
        string $number,
        \DateTimeImmutable $validUntil,
        ?string $notes = null
    ): self {
        return new self(
            QuoteId::generate(),
            $clientId,
            $number,
            new \DateTimeImmutable(),
            $validUntil,
            QuoteStatus::DRAFT,
            $notes
        );
    }

    public function id(): QuoteId
    {
        return $this->id;
    }

    public function clientId(): PersonId
    {
        return $this->clientId;
    }

    public function number(): string
    {
        return $this->number;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function validUntil(): \DateTimeImmutable
    {
        return $this->validUntil;
    }

    public function status(): QuoteStatus
    {
        return $this->status;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    /** @return QuoteLine[] */
    public function lines(): array
    {
        return $this->lines;
    }

    public function addLine(QuoteLine $line): void
    {
        if ($this->status !== QuoteStatus::DRAFT) {
            throw new \DomainException('Cannot add line to a non-draft quote');
        }
        $this->lines[] = $line;
    }

    public function removeLine(int $index): void
    {
        if ($this->status !== QuoteStatus::DRAFT) {
            throw new \DomainException('Cannot remove line from a non-draft quote');
        }
        if (!isset($this->lines[$index])) {
            throw new \InvalidArgumentException('Invalid line index');
        }
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function totalAmount(): Amount
    {
        $total = new Amount(0);
        foreach ($this->lines as $line) {
            $total = $total->add($line->totalAmount());
        }
        return $total;
    }

    public function send(): void
    {
        if ($this->status !== QuoteStatus::DRAFT) {
            throw new \DomainException('Can only send draft quotes');
        }
        if (empty($this->lines)) {
            throw new \DomainException('Cannot send empty quote');
        }
        $this->status = QuoteStatus::SENT;
    }

    public function accept(): void
    {
        if ($this->status !== QuoteStatus::SENT) {
            throw new \DomainException('Can only accept sent quotes');
        }
        $this->status = QuoteStatus::ACCEPTED;
    }

    public function reject(): void
    {
        if ($this->status !== QuoteStatus::SENT) {
            throw new \DomainException('Can only reject sent quotes');
        }
        $this->status = QuoteStatus::REJECTED;
    }

    public function markAsExpired(): void
    {
        if ($this->status === QuoteStatus::ACCEPTED || $this->status === QuoteStatus::REJECTED) {
            throw new \DomainException('Cannot mark accepted or rejected quote as expired');
        }
        $this->status = QuoteStatus::EXPIRED;
    }

    public function updateNotes(?string $notes): void
    {
        $this->notes = $notes;
    }
}
