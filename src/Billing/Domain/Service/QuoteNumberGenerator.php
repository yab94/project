<?php

declare(strict_types=1);

namespace App\Billing\Domain\Service;

use DateTimeInterface;

/**
 * Domain Service for generating unique quote numbers.
 * 
 * This is a Domain Service because:
 * - It encapsulates business logic (number format rules)
 * - It doesn't naturally belong to any entity
 * - It's a pure business concern, not technical
 */
final class QuoteNumberGenerator
{
    private const PREFIX = 'QT';

    /**
     * Generate a unique quote number based on issue date.
     * 
     * Format: QT-YYYYMMDD-{unique-suffix}
     * Example: QT-20251221-6768abc123def
     * 
     * @param DateTimeInterface $issueDate The date the quote is created
     * @return string The generated quote number
     */
    public function generate(DateTimeInterface $issueDate): string
    {
        $datePart = $issueDate->format('Ymd');
        $uniqueSuffix = uniqid('', true);
        
        return sprintf('%s-%s-%s', self::PREFIX, $datePart, $uniqueSuffix);
    }

    /**
     * Validate if a quote number follows the correct format.
     * 
     * @param string $number The quote number to validate
     * @return bool True if valid, false otherwise
     */
    public function isValid(string $number): bool
    {
        // Pattern: QT-YYYYMMDD-suffix
        $pattern = '/^QT-\d{8}-.+$/';
        return preg_match($pattern, $number) === 1;
    }

    /**
     * Extract the date from a quote number.
     * 
     * @param string $number The quote number
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
