<?php

declare(strict_types=1);

namespace App\CRM\Domain\ValueObject;

use App\Core\Domain\ValueObject;

enum PersonType: string implements ValueObject
{
    case INDIVIDUAL = 'individual';
    case COMPANY = 'company';

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this === $other;
    }
}
