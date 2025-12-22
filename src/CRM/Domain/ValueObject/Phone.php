<?php

declare(strict_types=1);

namespace App\CRM\Domain\ValueObject;

use App\Core\Domain\ValueObject;

final class Phone implements ValueObject
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

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }
}
