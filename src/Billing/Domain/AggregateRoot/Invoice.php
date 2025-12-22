<?php

declare(strict_types=1);

namespace App\Billing\Domain\AggregateRoot;

use App\Core\Domain\AggregateRoot;
use App\Billing\Domain\ValueObject\Amount;
use App\Billing\Domain\ValueObject\InvoiceId;
use App\Billing\Domain\ValueObject\InvoiceStatus;
use App\Billing\Domain\ValueObject\QuoteId;
use App\Billing\Domain\ValueObject\InvoiceLine;
use App\CRM\Domain\ValueObject\PersonId;

/**
 * Invoice Aggregate Root
 * 
 * Manages the Invoice aggregate which includes:
 * - InvoiceLine value objects (line items with description, quantity, price)
 * 
 * Enforces business rules:
 * - Lines can only be added/removed in DRAFT status
 * - Invoice workflow: draft → issued → paid/cancelled
 * - Cannot issue empty invoices
 * - Paid amount must match total amount
 * - Cannot cancel paid invoices
 * 
 * All modifications to invoice lines must go through Invoice to maintain
 * aggregate consistency and enforce workflow rules.
 */
final class Invoice implements AggregateRoot
{
    private InvoiceId $id;
    private PersonId $clientId;
    private ?QuoteId $quoteId;
    private string $number;
    private \DateTimeImmutable $issuedAt;
    private \DateTimeImmutable $dueDate;
    private InvoiceStatus $status;
    /** @var InvoiceLine[] */
    private array $lines = [];
    private ?string $notes;
    private ?Amount $paidAmount;

    public function __construct(
        InvoiceId $id,
        PersonId $clientId,
        ?QuoteId $quoteId,
        string $number,
        \DateTimeImmutable $issuedAt,
        \DateTimeImmutable $dueDate,
        InvoiceStatus $status = InvoiceStatus::DRAFT,
        ?string $notes = null
    ) {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->quoteId = $quoteId;
        $this->number = $number;
        $this->issuedAt = $issuedAt;
        $this->dueDate = $dueDate;
        $this->status = $status;
        $this->notes = $notes;
        $this->paidAmount = null;
    }

    public static function create(
        PersonId $clientId,
        string $number,
        \DateTimeImmutable $dueDate,
        ?QuoteId $quoteId = null,
        ?string $notes = null
    ): self {
        return new self(
            InvoiceId::generate(),
            $clientId,
            $quoteId,
            $number,
            new \DateTimeImmutable(),
            $dueDate,
            InvoiceStatus::DRAFT,
            $notes
        );
    }

    public function id(): InvoiceId
    {
        return $this->id;
    }

    public function clientId(): PersonId
    {
        return $this->clientId;
    }

    public function quoteId(): ?QuoteId
    {
        return $this->quoteId;
    }

    public function number(): string
    {
        return $this->number;
    }

    public function issuedAt(): \DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function dueDate(): \DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function status(): InvoiceStatus
    {
        return $this->status;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    public function paidAmount(): ?Amount
    {
        return $this->paidAmount;
    }

    /** @return InvoiceLine[] */
    public function lines(): array
    {
        return $this->lines;
    }

    public function addLine(InvoiceLine $line): void
    {
        if ($this->status !== InvoiceStatus::DRAFT) {
            throw new \DomainException('Cannot add line to a non-draft invoice');
        }
        $this->lines[] = $line;
    }

    public function removeLine(int $index): void
    {
        if ($this->status !== InvoiceStatus::DRAFT) {
            throw new \DomainException('Cannot remove line from a non-draft invoice');
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

    public function issue(): void
    {
        if ($this->status !== InvoiceStatus::DRAFT) {
            throw new \DomainException('Can only issue draft invoices');
        }
        if (empty($this->lines)) {
            throw new \DomainException('Cannot issue empty invoice');
        }
        $this->status = InvoiceStatus::ISSUED;
    }

    public function markAsPaid(Amount $paidAmount): void
    {
        if ($this->status !== InvoiceStatus::ISSUED) {
            throw new \DomainException('Can only mark issued invoices as paid');
        }
        if (!$paidAmount->equals($this->totalAmount())) {
            throw new \DomainException('Paid amount must equal total amount');
        }
        $this->status = InvoiceStatus::PAID;
        $this->paidAmount = $paidAmount;
    }

    public function cancel(): void
    {
        if ($this->status === InvoiceStatus::PAID) {
            throw new \DomainException('Cannot cancel paid invoice');
        }
        $this->status = InvoiceStatus::CANCELLED;
    }

    public function updateNotes(?string $notes): void
    {
        $this->notes = $notes;
    }
}
