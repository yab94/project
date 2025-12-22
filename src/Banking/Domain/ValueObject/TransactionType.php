<?php

declare(strict_types=1);

namespace App\Banking\Domain\ValueObject;

use App\Core\Domain\ValueObject;

enum TransactionType: string implements ValueObject
{
    case DEBIT = 'debit';
    case CREDIT = 'credit';

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this === $other;
    }
}
