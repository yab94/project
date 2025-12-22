<?php

declare(strict_types=1);

namespace App\Billing\Domain\Service;

use App\Billing\Domain\AggregateRoot\Invoice;
use App\Billing\Domain\ValueObject\Amount;

/**
 * Domain Service for calculating invoice totals and applying business rules.
 * 
 * This is a Domain Service because:
 * - It performs complex calculations involving the aggregate
 * - It may evolve to include tax calculations, late fees, etc.
 * - It's pure business logic
 */
final class InvoicePricingService
{
    /**
     * Calculate the total amount for an invoice including all lines.
     * 
     * @param Invoice $invoice The invoice to calculate
     * @return Amount The total amount
     */
    public function calculateTotal(Invoice $invoice): Amount
    {
        $total = new Amount(0);

        foreach ($invoice->lines() as $line) {
            $total = $total->add($line->totalAmount());
        }

        return $total;
    }

    /**
     * Calculate remaining balance to be paid.
     * 
     * @param Invoice $invoice The invoice
     * @return Amount The remaining balance
     */
    public function calculateRemainingBalance(Invoice $invoice): Amount
    {
        $total = $this->calculateTotal($invoice);
        $paid = $invoice->paidAmount() ?? new Amount(0);

        return new Amount($total->value() - $paid->value(), $total->currency());
    }

    /**
     * Check if invoice is fully paid.
     * 
     * @param Invoice $invoice The invoice to check
     * @return bool True if fully paid
     */
    public function isFullyPaid(Invoice $invoice): bool
    {
        $remaining = $this->calculateRemainingBalance($invoice);
        return $remaining->value() <= 0;
    }

    /**
     * Calculate tax amount (future feature - currently returns 0).
     * 
     * @param Invoice $invoice The invoice
     * @param float $taxRate Tax rate (e.g., 0.20 for 20%)
     * @return Amount The tax amount
     */
    public function calculateTax(Invoice $invoice, float $taxRate): Amount
    {
        if ($taxRate < 0 || $taxRate > 1) {
            throw new \InvalidArgumentException('Tax rate must be between 0 and 1');
        }

        $subtotal = $this->calculateTotal($invoice);
        return new Amount($subtotal->value() * $taxRate, $subtotal->currency());
    }
}
