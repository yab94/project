<?php

declare(strict_types=1);

namespace App\Billing\Domain\Service;

use DateTimeInterface;

/**
 * Domain Service for generating unique invoice numbers.
 * 
 * This is a Domain Service because:
 * - It encapsulates business logic (number format rules)
 * - It doesn't naturally belong to any entity
 * - It's a pure business concern, not technical
 */
final class InvoiceNumberGenerator
{
    private const PREFIX = 'INV';

    /**
     * Generate a unique invoice number based on issue date.
     * 
     * Format: INV-YYYYMMDD-{unique-suffix}
     * Example: INV-20251221-6768abc123def
     * 
     * @param DateTimeInterface $issueDate The date the invoice is created
     * @return string The generated invoice number
     */
    public function generate(DateTimeInterface $issueDate): string
    {
        $datePart = $issueDate->format('Ymd');
        $uniqueSuffix = uniqid('', true);
        
        return sprintf('%s-%s-%s', self::PREFIX, $datePart, $uniqueSuffix);
    }

    /**
     * Validate if an invoice number follows the correct format.
     * 
     * @param string $number The invoice number to validate
     * @return bool True if valid, false otherwise
     */
    public function isValid(string $number): bool
    {
        // Pattern: INV-YYYYMMDD-suffix
        $pattern = '/^INV-\d{8}-.+$/';
        return preg_match($pattern, $number) === 1;
    }

    /**
     * Extract the date from an invoice number.
     * 
     * @param string $number The invoice number
     * @return string|null The date in YYYYMMDD format, or null if invalid
     */
    public function extractDate(string $number): ?string
    {
        if (!$this->isValid($number)) {
            return null;
        }

        $parts = explode('-', $number);
        return $parts[1] ?? null;
    }
}
