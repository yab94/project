<?php

declare(strict_types=1);

namespace App\Banking\Domain\Repository;

use App\Banking\Domain\AggregateRoot\BankAccount;
use App\Banking\Domain\ValueObject\BankAccountId;
use App\Core\Domain\Repository;

interface BankAccountRepositoryInterface extends Repository
{
    public function save(BankAccount $account): void;
    
    public function findById(BankAccountId $id): ?BankAccount;
    
    /** @return BankAccount[] */
    public function findAll(): array;
    
    public function delete(BankAccountId $id): void;
}
