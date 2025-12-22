<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Billing\Domain\ValueObject\Amount;

final class AmountTest extends TestCase
{
    public function testCreateAmount(): void
    {
        $amount = new Amount(100.50);
        $this->assertEquals(100.50, $amount->value());
    }

    public function testNegativeAmountThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Amount(-10);
    }

    public function testAdd(): void
    {
        $amount1 = new Amount(100);
        $amount2 = new Amount(50);
        $result = $amount1->add($amount2);
        
        $this->assertEquals(150, $result->value());
    }

    public function testSubtract(): void
    {
        $amount1 = new Amount(100);
        $amount2 = new Amount(30);
        $result = $amount1->subtract($amount2);
        
        $this->assertEquals(70, $result->value());
    }

    public function testSubtractMoreThanAvailableThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $amount1 = new Amount(50);
        $amount2 = new Amount(100);
        $amount1->subtract($amount2);
    }

    public function testMultiply(): void
    {
        $amount = new Amount(25);
        $result = $amount->multiply(4);
        
        $this->assertEquals(100, $result->value());
    }

    public function testEquals(): void
    {
        $amount1 = new Amount(100);
        $amount2 = new Amount(100);
        $amount3 = new Amount(150);

        $this->assertTrue($amount1->equals($amount2));
        $this->assertFalse($amount1->equals($amount3));
    }
}
