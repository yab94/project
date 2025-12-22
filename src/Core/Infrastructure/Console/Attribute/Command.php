<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Console\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Command
{
    public function __construct(
        public readonly string $name,
        public readonly string $description = ''
    ) {
    }
}
