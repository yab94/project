<?php

declare(strict_types=1);

namespace App\CRM;

use App\Core\Domain\AbstractModule as BaseModule;
use App\CRM\Infrastructure\Web\Controller\PersonController;
use App\CRM\Infrastructure\Console\Command\TestCommand;
use App\CRM\Infrastructure\Console\Command\ListPersonsCommand;

class Module extends BaseModule
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
