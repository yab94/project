<?php

declare(strict_types=1);

namespace App\Billing\Domain\ValueObject;

use App\Core\Domain\ValueObjectInterface;

enum QuoteStatus: string implements ValueObjectInterface
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this === $other;
    }
}
