<?php

declare(strict_types=1);

namespace App\Billing\Application\Command;

final class CreateInvoiceCommand
{
    public function __construct(
        public readonly string $quoteId,
        public readonly \DateTimeImmutable $issueDate,
        public readonly \DateTimeImmutable $dueDate
    ) {}
}
