<?php

declare(strict_types=1);

namespace App\Core\Domain;

/**
 * Marker interface for Aggregate Roots in Domain-Driven Design.
 * 
 * An Aggregate is a cluster of domain objects that can be treated as a single unit.
 * The Aggregate Root is the entry point to the aggregate and ensures consistency.
 * 
 * An Aggregate Root is a special type of Entity that:
 * - Serves as the entry point to an aggregate
 * - Enforces consistency boundaries
 * - Controls access to internal entities and value objects
 * 
 * Rules:
 * - All external access to the aggregate must go through the Aggregate Root
 * - The root is responsible for enforcing invariants across the aggregate
 * - Only Aggregate Roots can be obtained directly from repositories
 * - Child entities should only be accessed through their root
 * 
 * Aggregate Roots in this system:
 * - Person (manages Address and Contact value objects)
 * - Quote (manages QuoteLine value objects)
 * - Invoice (manages InvoiceLine value objects)
 * - BankAccount (manages BankTransaction entities)
 */
interface AggregateRootInterface extends EntityInterface
{
    // Inherits id(): mixed from EntityInterface
    // No additional methods required - this is a marker interface
}
