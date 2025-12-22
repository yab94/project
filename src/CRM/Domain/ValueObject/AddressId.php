<?php

declare(strict_types=1);

namespace App\CRM\Domain\ValueObject;

use App\Core\Domain\ValueObjectInterface;

final class AddressId implements ValueObjectInterface
{
    private string $value;

    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException('AddressId cannot be empty');
        }
        $this->value = $value;
    }

    public static function generate(): self
    {
        return new self(uniqid('addr_', true));
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
