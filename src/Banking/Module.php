<?php

declare(strict_types=1);

namespace App\Banking;

use App\Banking\Infrastructure\Web\Controller\BankAccountController;
use App\Core\Domain\ModuleInterface;

final class Module implements ModuleInterface
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
