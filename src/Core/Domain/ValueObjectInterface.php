<?php

declare(strict_types=1);

namespace App\Core\Domain;

/**
 * Value Object Marker Interface
 * 
 * In Domain-Driven Design, Value Objects are immutable objects that are defined
 * by their attributes rather than by identity. They have no concept of identity
 * and two value objects with the same attributes are considered equal.
 * 
 * Key characteristics of Value Objects:
 * 
 * 1. Immutability: Once created, their state cannot be changed
 * 2. Equality by value: Two value objects are equal if all their attributes are equal
 * 3. No identity: They don't have a unique identifier (unlike Entities)
 * 4. Side-effect free: Their methods should not have side effects
 * 5. Replaceability: If you want to change a value, create a new instance
 * 
 * Examples in this system:
 * - PersonId, QuoteId, InvoiceId, BankAccountId (Identity Value Objects)
 * - Amount (Money pattern)
 * - IBAN (validated bank identifier)
 * - Address (structural value object)
 * - Contact (email/phone combination)
 * - QuoteLine, InvoiceLine (line items)
 * - PersonType, QuoteStatus, InvoiceStatus, TransactionType (Enums as Value Objects)
 * 
 * Implementation guidelines:
 * - Make properties readonly (PHP 8.1+)
 * - Implement equals() method for value comparison
 * - Validate in constructor
 * - Return new instances for modifications (immutability)
 * - Override __toString() for debugging
 */
interface ValueObjectInterface
{
    /**
     * Compare this value object with another for equality
     * 
     * @param ValueObjectInterface $other The value object to compare with
     * @return bool True if both value objects have equal values
     */
    public function equals(ValueObjectInterface $other): bool;
}
