<?php

declare(strict_types=1);

namespace App\Billing\Domain\ValueObject;

use App\Core\Domain\ValueObject;

enum QuoteStatus: string implements ValueObject
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this === $other;
    }
}
