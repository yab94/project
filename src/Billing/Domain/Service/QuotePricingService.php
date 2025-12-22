<?php

declare(strict_types=1);

namespace App\Billing\Domain\Service;

use App\Billing\Domain\AggregateRoot\Quote;
use App\Billing\Domain\ValueObject\QuoteLine;
use App\Billing\Domain\ValueObject\Amount;

/**
 * Domain Service for calculating quote totals and applying business rules.
 * 
 * This is a Domain Service because:
 * - It performs complex calculations involving the aggregate
 * - It may evolve to include discount rules, tax calculations, etc.
 * - It's pure business logic
 */
final class QuotePricingService
{
    /**
     * Calculate the total amount for a quote including all lines.
     * 
     * @param Quote $quote The quote to calculate
     * @return Amount The total amount
     */
    public function calculateTotal(Quote $quote): Amount
    {
        $total = new Amount(0);

        foreach ($quote->lines() as $line) {
            $total = $total->add($line->totalAmount());
        }

        return $total;
    }

    /**
     * Calculate total with potential discount (future feature).
     * 
     * @param Quote $quote The quote
     * @param float $discountPercentage Discount percentage (0-100)
     * @return Amount The discounted total
     */
    public function calculateTotalWithDiscount(Quote $quote, float $discountPercentage): Amount
    {
        if ($discountPercentage < 0 || $discountPercentage > 100) {
            throw new \InvalidArgumentException('Discount must be between 0 and 100');
        }

        $total = $this->calculateTotal($quote);
        $discountMultiplier = 1 - ($discountPercentage / 100);

        return new Amount($total->value() * $discountMultiplier, $total->currency());
    }

    /**
     * Check if a quote exceeds a certain amount threshold.
     * 
     * @param Quote $quote The quote to check
     * @param Amount $threshold The threshold amount
     * @return bool True if quote total exceeds threshold
     */
    public function exceedsThreshold(Quote $quote, Amount $threshold): bool
    {
        $total = $this->calculateTotal($quote);
        return $total->value() > $threshold->value();
    }
}
