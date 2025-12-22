<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\CRM\Module as CRMModule;
use App\Billing\Module as BillingModule;
use App\Banking\Module as BankingModule;
use App\CRM\Infrastructure\Web\Controller\PersonController;
use App\Billing\Infrastructure\Web\Controller\QuoteController;
use App\Billing\Infrastructure\Web\Controller\InvoiceController;

class ModuleTest extends TestCase
{
    public function testCRMModuleReturnsCorrectName(): void
    {
        $module = new CRMModule();
        $this->assertEquals('CRM', $module->getName());
    }

    public function testCRMModuleReturnsControllers(): void
    {
        $module = new CRMModule();
        $controllers = $module->getControllers();

        $this->assertIsArray($controllers);
        $this->assertContains(PersonController::class, $controllers);
    }

    public function testBillingModuleReturnsCorrectName(): void
    {
        $module = new BillingModule();
        $this->assertEquals('Billing', $module->getName());
    }

    public function testBillingModuleReturnsControllers(): void
    {
        $module = new BillingModule();
        $controllers = $module->getControllers();

        $this->assertIsArray($controllers);
        $this->assertContains(QuoteController::class, $controllers);
        $this->assertContains(InvoiceController::class, $controllers);
        $this->assertCount(2, $controllers);
    }

    public function testBankingModuleReturnsCorrectName(): void
    {
        $module = new BankingModule();
        $this->assertEquals('Banking', $module->getName());
    }

    public function testBankingModuleReturnsEmptyControllers(): void
    {
        $module = new BankingModule();
        $controllers = $module->getControllers();

        $this->assertIsArray($controllers);
        $this->assertEmpty($controllers);
    }

    public function testModuleBootCanBeCalled(): void
    {
        $module = new CRMModule();
        
        // Should not throw exception
        $module->boot();
        
        $this->assertTrue(true);
    }
}
