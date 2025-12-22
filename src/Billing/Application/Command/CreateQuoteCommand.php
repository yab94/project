<?php

declare(strict_types=1);

namespace App\Billing\Application\Command;

final class CreateQuoteCommand
{
    public function __construct(
        public readonly string $personId,
        public readonly \DateTimeImmutable $issueDate,
        public readonly \DateTimeImmutable $expiryDate
    ) {}
}
