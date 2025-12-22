<?php

declare(strict_types=1);

namespace App\Banking\Domain\ValueObject;

use App\Core\Domain\ValueObjectInterface;

enum TransactionType: string implements ValueObjectInterface
{
    case DEBIT = 'debit';
    case CREDIT = 'credit';

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this === $other;
    }
}
