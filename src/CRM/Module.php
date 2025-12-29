<?php

declare(strict_types=1);

namespace App\CRM;

use App\Core\Domain\ModuleInterface;
use App\CRM\Infrastructure\Web\Controller\PersonController;
use App\CRM\Infrastructure\Console\Command\TestCommand;
use App\CRM\Infrastructure\Console\Command\ListPersonsCommand;

final class Module implements ModuleInterface
{
    public function getName(): string
    {
        return 'CRM';
    }

    public function getControllers(): array
    {
        return [
            PersonController::class,
        ];
    }

    public function getCommands(): array
    {
        return [
            TestCommand::class,
            ListPersonsCommand::class,
        ];
    }
}
