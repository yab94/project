<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Web\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Delete extends Route
{
    public function __construct(string $path, ?string $name = null)
    {
        parent::__construct($path, 'DELETE', $name);
    }
}
