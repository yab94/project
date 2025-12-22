<?php

declare(strict_types=1);

namespace App\Billing\Domain\ValueObject;

use App\Core\Domain\ValueObjectInterface;

final readonly class Amount implements ValueObjectInterface
{
    private float $value;
    private string $currency;

    public function __construct(float $value, string $currency = 'EUR')
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('Amount cannot be negative');
        }
        $this->value = round($value, 2);
        $this->currency = $currency;
    }

    public function value(): float
    {
        return $this->value;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function add(Amount $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot add amounts with different currencies');
        }
        return new self($this->value + $other->value, $this->currency);
    }

    public function subtract(Amount $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot subtract amounts with different currencies');
        }
        return new self($this->value - $other->value, $this->currency);
    }

    public function multiply(float $multiplier): self
    {
        return new self($this->value * $multiplier, $this->currency);
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self 
            && $this->value === $other->value 
            && $this->currency === $other->currency;
    }
}
