<?php

declare(strict_types=1);

namespace App\Billing\Domain\ValueObject;

use App\Core\Domain\ValueObjectInterface;

enum InvoiceStatus: string implements ValueObjectInterface
{
    case DRAFT = 'draft';
    case ISSUED = 'issued';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this === $other;
    }
}
