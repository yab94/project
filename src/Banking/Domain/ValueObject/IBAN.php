<?php

declare(strict_types=1);

namespace App\Banking\Domain\ValueObject;

use App\Core\Domain\ValueObject;

final readonly class IBAN implements ValueObject
{
    private string $value;

    public function __construct(string $value)
    {
        $cleaned = preg_replace('/\s+/', '', $value);
        if (!$this->isValid($cleaned)) {
            throw new \InvalidArgumentException("Invalid IBAN: {$value}");
        }
        $this->value = $cleaned;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function formatted(): string
    {
        return chunk_split($this->value, 4, ' ');
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }

    private function isValid(string $iban): bool
    {
        if (strlen($iban) < 15 || strlen($iban) > 34) {
            return false;
        }
        if (!preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/', $iban)) {
            return false;
        }
        return true;
    }
}
