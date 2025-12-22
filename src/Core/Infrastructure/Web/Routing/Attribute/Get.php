<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Web\Routing\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Get extends Route
{
    public function __construct(string $path, ?string $name = null)
    {
        parent::__construct($path, 'GET', $name);
    }
}
