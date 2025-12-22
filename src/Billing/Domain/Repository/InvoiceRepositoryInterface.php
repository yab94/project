<?php

declare(strict_types=1);

namespace App\Billing\Domain\Repository;

use App\Billing\Domain\AggregateRoot\Invoice;
use App\Billing\Domain\ValueObject\InvoiceId;
use App\CRM\Domain\ValueObject\PersonId;
use App\Core\Domain\Repository;

interface InvoiceRepositoryInterface extends Repository
{
    public function save(Invoice $invoice): void;
    
    public function findById(InvoiceId $id): ?Invoice;
    
    /** @return Invoice[] */
    public function findAll(): array;
    
    /** @return Invoice[] */
    public function findByClientId(PersonId $clientId): array;
    
    public function delete(InvoiceId $id): void;
}
