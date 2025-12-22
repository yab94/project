<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Web\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(
        public readonly string $path,
        public readonly string $method = 'GET',
        public readonly ?string $name = null
    ) {
    }
}
