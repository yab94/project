<?php

declare(strict_types=1);

namespace App\CRM\Domain\Service;

use App\CRM\Domain\AggregateRoot\Person;
use App\CRM\Domain\ValueObject\PersonType;

/**
 * Domain Service for Person-related business rules and validations.
 * 
 * This is a Domain Service because:
 * - It encapsulates business rules that span multiple entities
 * - It provides domain-specific validations
 * - It's pure business logic
 */
final class PersonValidator
{
    /**
     * Validate if a person can be deleted.
     * 
     * Business rules:
     * - Cannot delete if person has active quotes/invoices (future check)
     * - Cannot delete if person has outstanding balance (future check)
     * 
     * @param Person $person The person to validate
     * @return bool True if can be deleted
     */
    public function canBeDeleted(Person $person): bool
    {
        // For now, always allow deletion
        // In production, would check:
        // - Active quotes/invoices
        // - Outstanding balances
        // - Historical data retention policies
        return true;
    }

    /**
     * Validate if a company name is appropriate.
     * 
     * @param string $companyName The company name to validate
     * @return bool True if valid
     */
    public function isValidCompanyName(string $companyName): bool
    {
        $trimmed = trim($companyName);
        
        // Minimum length
        if (strlen($trimmed) < 2) {
            return false;
        }

        // Maximum length
        if (strlen($trimmed) > 200) {
            return false;
        }

        // Cannot be just numbers
        if (preg_match('/^\d+$/', $trimmed)) {
            return false;
        }

        return true;
    }

    /**
     * Check if a person has complete contact information.
     * 
     * @param Person $person The person to check
     * @return bool True if has at least one contact method
     */
    public function hasCompleteContactInfo(Person $person): bool
    {
        return count($person->contacts()) > 0;
    }

    /**
     * Check if a person has a billing address.
     * 
     * @param Person $person The person to check
     * @return bool True if has at least one address
     */
    public function hasBillingAddress(Person $person): bool
    {
        return count($person->addresses()) > 0;
    }

    /**
     * Validate if person is ready for invoicing.
     * 
     * A person must have complete info to receive invoices.
     * 
     * @param Person $person The person to validate
     * @return bool True if ready for invoicing
     */
    public function isReadyForInvoicing(Person $person): bool
    {
        return $this->hasBillingAddress($person) 
            && $this->hasCompleteContactInfo($person);
    }
}
