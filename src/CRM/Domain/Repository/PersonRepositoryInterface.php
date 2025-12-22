<?php

declare(strict_types=1);

namespace App\CRM\Domain\Repository;

use App\CRM\Domain\AggregateRoot\Person;
use App\CRM\Domain\ValueObject\PersonId;
use App\Core\Domain\Repository;

interface PersonRepositoryInterface extends Repository
{
    public function save(Person $person): void;
    
    public function findById(PersonId $id): ?Person;
    
    /** @return Person[] */
    public function findAll(): array;
    
    public function delete(PersonId $id): void;
}
