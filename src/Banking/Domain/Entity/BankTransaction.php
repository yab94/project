<?php

declare(strict_types=1);

namespace App\Banking\Domain\Entity;

use App\Core\Domain\Entity;
use App\Banking\Domain\ValueObject\BankAccountId;
use App\Banking\Domain\ValueObject\BankTransactionId;
use App\Banking\Domain\ValueObject\TransactionType;
use App\Billing\Domain\ValueObject\Amount;
use App\Billing\Domain\ValueObject\InvoiceId;

final class BankTransaction implements Entity
{
    private BankTransactionId $id;
    private BankAccountId $bankAccountId;
    private \DateTimeImmutable $date;
    private TransactionType $type;
    private Amount $amount;
    private string $label;
    private ?InvoiceId $invoiceId;
    private bool $reconciled;

    public function __construct(
        BankTransactionId $id,
        BankAccountId $bankAccountId,
        \DateTimeImmutable $date,
        TransactionType $type,
        Amount $amount,
        string $label,
        ?InvoiceId $invoiceId = null,
        bool $reconciled = false
    ) {
        $this->id = $id;
        $this->bankAccountId = $bankAccountId;
        $this->date = $date;
        $this->type = $type;
        $this->amount = $amount;
        $this->setLabel($label);
        $this->invoiceId = $invoiceId;
        $this->reconciled = $reconciled;
    }

    public static function create(
        BankAccountId $bankAccountId,
        \DateTimeImmutable $date,
        TransactionType $type,
        Amount $amount,
        string $label
    ): self {
        return new self(
            BankTransactionId::generate(),
            $bankAccountId,
            $date,
            $type,
            $amount,
            $label
        );
    }

    public function id(): BankTransactionId
    {
        return $this->id;
    }

    public function bankAccountId(): BankAccountId
    {
        return $this->bankAccountId;
    }

    public function date(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function type(): TransactionType
    {
        return $this->type;
    }

    public function amount(): Amount
    {
        return $this->amount;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function invoiceId(): ?InvoiceId
    {
        return $this->invoiceId;
    }

    public function isReconciled(): bool
    {
        return $this->reconciled;
    }

    public function linkToInvoice(InvoiceId $invoiceId): void
    {
        if ($this->type !== TransactionType::CREDIT) {
            throw new \DomainException('Only credit transactions can be linked to invoices');
        }
        $this->invoiceId = $invoiceId;
    }

    public function unlinkFromInvoice(): void
    {
        $this->invoiceId = null;
    }

    public function reconcile(): void
    {
        $this->reconciled = true;
    }

    public function cancelReconciliation(): void
    {
        $this->reconciled = false;
    }

    public function updateLabel(string $label): void
    {
        $this->setLabel($label);
    }

    private function setLabel(string $label): void
    {
        if (empty(trim($label))) {
            throw new \InvalidArgumentException('Label cannot be empty');
        }
        $this->label = $label;
    }
}
