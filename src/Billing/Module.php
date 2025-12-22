<?php

declare(strict_types=1);

namespace App\Billing;

use App\Core\Domain\AbstractModule as BaseModule;
use App\Billing\Infrastructure\Web\Controller\QuoteController;
use App\Billing\Infrastructure\Web\Controller\InvoiceController;

class Module extends BaseModule
{
    public function getName(): string
    {
        return 'Billing';
    }

    public function getControllers(): array
    {
        return [
            QuoteController::class,
            InvoiceController::class,
        ];
    }

    public function getCommands(): array
    {
        return [];
    }
}
