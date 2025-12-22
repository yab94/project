<?php

declare(strict_types=1);

namespace App\Core\Domain;

/**
 * Base Repository interface for all domain repositories.
 * 
 * All repositories should provide these common operations:
 * - save($entity): void - Create or update an entity
 * - findById($id): ?Entity - Find an entity by its identifier
 * - findAll(): array - Find all entities
 * - delete($id): void - Delete an entity by its identifier
 * 
 * Each aggregate root should have its own repository interface extending this one,
 * providing type-specific methods for that aggregate.
 * 
 * Example:
 * ```php
 * interface PersonRepositoryInterface extends RepositoryInterface
 * {
 *     public function save(Person $person): void;
 *     public function findById(PersonId $id): ?Person;
 *     public function findAll(): array;
 *     public function delete(PersonId $id): void;
 * }
 * ```
 */
interface RepositoryInterface
{
    // Marker interface - concrete repositories define their own type-specific methods
    // This allows for proper type safety while maintaining a common contract
}
