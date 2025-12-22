<?php

declare(strict_types=1);

namespace App\Billing\Domain\ValueObject;

use App\Core\Domain\ValueObjectInterface;

final readonly class QuoteId implements ValueObjectInterface
{
    private string $value;

    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException('QuoteId cannot be empty');
        }
        $this->value = $value;
    }

    public static function generate(): self
    {
        return new self(uniqid('quote_', true));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }
}
