<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Domain\Module as BaseModule;

/**
 * Core Module - Core module that orchestrates all other modules
 */
class Module extends BaseModule
{
    public function getName(): string
    {
        return 'Core';
    }

    public function getControllers(): array
    {
        return [
            \App\Core\Infrastructure\Web\Controller\HomeController::class,
        ];
    }

    public function getCommands(): array
    {
        return [];
    }
}
