<?php

declare(strict_types=1);

namespace App\CRM\Domain\ValueObject;

use App\Core\Domain\ValueObjectInterface;

enum PersonType: string implements ValueObjectInterface
{
    case INDIVIDUAL = 'individual';
    case COMPANY = 'company';

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self && $this === $other;
    }
}
