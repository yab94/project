<?php

declare(strict_types=1);

namespace App\Core\Domain;

/**
 * Entity marker interface.
 * 
 * An Entity is an object that has a unique identity that persists over time,
 * independent of its attributes. Two entities are considered equal if they
 * have the same identity, even if their attributes differ.
 * 
 * Characteristics:
 * - Has a unique identifier (ID)
 * - Identity-based equality (not attribute-based)
 * - Can be mutable (attributes can change over time)
 * - Continuity through time (same entity despite changes)
 * 
 * Examples:
 * - BankTransaction: has BankTransactionId, can be modified, but always the same transaction
 * - Person: has PersonId, can change name/address, but same person
 * 
 * Difference with Value Object:
 * - Value Object: no identity, equality by attributes, immutable
 * - Entity: has identity, equality by ID, can be mutable
 * 
 * Note: Aggregate Roots are special entities that serve as entry points
 * to an aggregate and enforce consistency boundaries.
 */
interface Entity
{
    /**
     * Get the unique identifier of this entity.
     * 
     * @return mixed The entity's ID (typically a Value Object)
     */
    public function id(): mixed;
}
