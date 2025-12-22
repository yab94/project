<?php

declare(strict_types=1);

namespace App\Layout;

use App\Core\Domain\Module as BaseModule;
use App\Layout\Infrastructure\Web\Controller\ErrorController;
use App\Layout\Infrastructure\Web\Controller\HomeController;

final class Module extends BaseModule
{
    public function getName(): string
    {
        return 'Layout';
    }

    public function getControllers(): array
    {
        return [
            HomeController::class,
            ErrorController::class,
        ];
    }

    public function getCommands(): array
    {
        return [];
    }
}
