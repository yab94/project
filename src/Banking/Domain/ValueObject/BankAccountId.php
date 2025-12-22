<?php

declare(strict_types=1);

namespace App\Banking\Domain\ValueObject;

use App\Core\Domain\ValueObject;

final readonly class BankAccountId implements ValueObject
{
    private string $value;

    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException('BankAccountId cannot be empty');
        }
        $this->value = $value;
    }

    public static function generate(): self
    {
        return new self(uniqid('account_', true));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }
}
