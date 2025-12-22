<?php

declare(strict_types=1);

namespace App\Billing\Domain\ValueObject;

use App\Billing\Domain\ValueObject\Amount;
use App\Core\Domain\ValueObject;

final readonly class QuoteLine implements ValueObject
{
    private string $description;
    private int $quantity;
    private Amount $unitPrice;
    private ?string $details;

    public function __construct(
        string $description,
        int $quantity,
        Amount $unitPrice,
        ?string $details = null
    ) {
        if (empty(trim($description))) {
            throw new \InvalidArgumentException('Description cannot be empty');
        }
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }
        $this->description = $description;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->details = $details;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function unitPrice(): Amount
    {
        return $this->unitPrice;
    }

    public function details(): ?string
    {
        return $this->details;
    }

    public function totalAmount(): Amount
    {
        return $this->unitPrice->multiply($this->quantity);
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->description === $other->description
            && $this->quantity === $other->quantity
            && $this->unitPrice->equals($other->unitPrice)
            && $this->details === $other->details;
    }
}
