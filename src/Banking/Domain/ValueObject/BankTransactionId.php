<?php

declare(strict_types=1);

namespace App\Banking\Domain\ValueObject;

final class BankTransactionId
{
    private string $value;

    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException('BankTransactionId cannot be empty');
        }
        $this->value = $value;
    }

    public static function generate(): self
    {
        return new self(uniqid('transaction_', true));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(BankTransactionId $other): bool
    {
        return $this->value === $other->value;
    }
}
