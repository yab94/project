<?php

declare(strict_types=1);

namespace App\Billing\Domain\Repository;

use App\Billing\Domain\AggregateRoot\Quote;
use App\Billing\Domain\ValueObject\QuoteId;
use App\CRM\Domain\ValueObject\PersonId;
use App\Core\Domain\Repository;

interface QuoteRepositoryInterface extends Repository
{
    public function save(Quote $quote): void;
    
    public function findById(QuoteId $id): ?Quote;
    
    /** @return Quote[] */
    public function findAll(): array;
    
    /** @return Quote[] */
    public function findByClientId(PersonId $clientId): array;
    
    public function delete(QuoteId $id): void;
}
