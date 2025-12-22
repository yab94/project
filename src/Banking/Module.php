<?php

declare(strict_types=1);

namespace App\Banking;

use App\Banking\Infrastructure\Web\Controller\BankAccountController;
use App\Core\Domain\AbstractModule as BaseModule;

class Module extends BaseModule
{
    public function getName(): string
    {
        return 'Banking';
    }

    public function getControllers(): array
    {
        return [
            BankAccountController::class,
        ];
    }

    public function getCommands(): array
    {
        return [
            // TODO: Add Banking commands when implemented
        ];
    }
}
