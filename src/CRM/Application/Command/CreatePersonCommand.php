<?php

declare(strict_types=1);

namespace App\CRM\Application\Command;

final class CreatePersonCommand
{
    public function __construct(
        public readonly string $type,
        public readonly string $name,
        public readonly ?string $siret = null
    ) {}
}
