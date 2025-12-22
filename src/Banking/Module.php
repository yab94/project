<?php

declare(strict_types=1);

namespace App\Banking;

use App\Banking\Application\BankAccountService;
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

    /**
     * Public API of the Banking module
     * Only these services can be used by other modules
     */
    public function getServices(): array
    {
        return [
            BankAccountService::class,
            // Add other services that should be accessible
        ];
    }
}
