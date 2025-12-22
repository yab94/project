<?php

declare(strict_types=1);

namespace App\Billing\Domain\ValueObject;

use App\Core\Domain\ValueObject;

enum InvoiceStatus: string implements ValueObject
{
    case DRAFT = 'draft';
    case ISSUED = 'issued';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this === $other;
    }
}
