<?php

declare(strict_types=1);

namespace App\Billing\Application\Command;

final class AddQuoteLineCommand
{
    public function __construct(
        public readonly string $quoteId,
        public readonly string $description,
        public readonly int $quantity,
        public readonly float $unitPrice
    ) {}
}
