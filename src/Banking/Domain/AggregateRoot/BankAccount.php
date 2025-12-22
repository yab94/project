<?php

declare(strict_types=1);

namespace App\Banking\Domain\AggregateRoot;

use App\Core\Domain\AggregateRoot;
use App\Banking\Domain\ValueObject\BankAccountId;
use App\Banking\Domain\ValueObject\IBAN;
use App\Billing\Domain\ValueObject\Amount;

/**
 * BankAccount Aggregate Root
 * 
 * Manages the BankAccount aggregate and ensures consistency of:
 * - Account balance
 * - IBAN and BIC information
 * 
 * Enforces business rules:
 * - Balance is updated through credit/debit operations
 * - IBAN validation
 * 
 * Note: BankTransaction entities are managed separately in this design,
 * but they reference BankAccount. In a stricter aggregate design,
 * transactions could be child entities of BankAccount.
 */
final class BankAccount implements AggregateRoot
{
    private BankAccountId $id;
    private string $name;
    private IBAN $iban;
    private ?string $bic;
    private Amount $balance;

    public function __construct(
        BankAccountId $id,
        string $name,
        IBAN $iban,
        ?string $bic = null,
        ?Amount $balance = null
    ) {
        $this->id = $id;
        $this->setName($name);
        $this->iban = $iban;
        $this->bic = $bic;
        $this->balance = $balance ?? new Amount(0);
    }

    public static function create(
        string $name,
        string $iban,
        ?string $bic = null
    ): self {
        return new self(
            BankAccountId::generate(),
            $name,
            new IBAN($iban),
            $bic
        );
    }

    public function id(): BankAccountId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function iban(): IBAN
    {
        return $this->iban;
    }

    public function bic(): ?string
    {
        return $this->bic;
    }

    public function balance(): Amount
    {
        return $this->balance;
    }

    public function credit(Amount $amount): void
    {
        $this->balance = $this->balance->add($amount);
    }

    public function debit(Amount $amount): void
    {
        $this->balance = $this->balance->subtract($amount);
    }

    public function updateName(string $name): void
    {
        $this->setName($name);
    }

    public function updateIBAN(IBAN $iban): void
    {
        $this->iban = $iban;
    }

    public function updateBIC(?string $bic): void
    {
        $this->bic = $bic;
    }

    private function setName(string $name): void
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('Name cannot be empty');
        }
        $this->name = $name;
    }
}
