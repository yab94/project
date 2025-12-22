<?php

declare(strict_types=1);

namespace App\CRM\Domain\ValueObject;

use App\Core\Domain\ValueObjectInterface;

final class Phone implements ValueObjectInterface
{
    private string $value;

    public function __construct(string $value)
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $value);
        if (empty($cleaned)) {
            throw new \InvalidArgumentException("Invalid phone number: {$value}");
        }
        $this->value = $cleaned;
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
