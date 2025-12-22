<?php

declare(strict_types=1);

namespace App\Banking\Infrastructure\Persistence;

use App\Banking\Domain\AggregateRoot\BankAccount;
use App\Banking\Domain\Repository\BankAccountRepositoryInterface;
use App\Banking\Domain\ValueObject\BankAccountId;
use App\Banking\Domain\ValueObject\IBAN;
use App\Billing\Domain\ValueObject\Amount;
use App\Core\Infrastructure\Persistence\Database;

final class PDOBankAccountRepository implements BankAccountRepositoryInterface
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function save(BankAccount $account): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO bank_accounts (id, name, iban, bic, balance)
            VALUES (:id, :name, :iban, :bic, :balance)
            ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                iban = VALUES(iban),
                bic = VALUES(bic),
                balance = VALUES(balance)
        ');

        $stmt->execute([
            'id' => $account->id()->value(),
            'name' => $account->name(),
            'iban' => $account->iban()->value(),
            'bic' => $account->bic(),
            'balance' => $account->balance()->value(),
        ]);
    }

    public function findById(BankAccountId $id): ?BankAccount
    {
        $stmt = $this->pdo->prepare('SELECT * FROM bank_accounts WHERE id = :id');
        $stmt->execute(['id' => $id->value()]);
        
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    /** @return BankAccount[] */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM bank_accounts ORDER BY name');
        $rows = $stmt->fetchAll();

        return array_map([$this, 'hydrate'], $rows);
    }

    public function delete(BankAccountId $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM bank_accounts WHERE id = :id');
        $stmt->execute(['id' => $id->value()]);
    }

    private function hydrate(array $row): BankAccount
    {
        return new BankAccount(
            new BankAccountId($row['id']),
            $row['name'],
            new IBAN($row['iban']),
            $row['bic'],
            new Amount((float) $row['balance'])
        );
    }
}
