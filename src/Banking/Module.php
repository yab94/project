<?php

declare(strict_types=1);

namespace App\Banking;

use App\Core\Domain\Module as BaseModule;

class Module extends BaseModule
{
    public function getName(): string
    {
        return 'Banking';
    }

    public function getControllers(): array
    {
        return [
            // TODO: Add Banking controllers when implemented
        ];
    }

    public function getCommands(): array
    {
        return [
            // TODO: Add Banking commands when implemented
        ];
    }
}
