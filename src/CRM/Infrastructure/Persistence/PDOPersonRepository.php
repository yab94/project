<?php

declare(strict_types=1);

namespace App\CRM\Infrastructure\Persistence;

use App\CRM\Domain\AggregateRoot\Person;
use App\CRM\Domain\Repository\PersonRepositoryInterface;
use App\CRM\Domain\ValueObject\PersonId;
use App\CRM\Domain\ValueObject\PersonType;
use App\Core\Infrastructure\Persistence\Database;

final class PDOPersonRepository implements PersonRepositoryInterface
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function save(Person $person): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO persons (id, type, name, first_name, company_name)
            VALUES (:id, :type, :name, :first_name, :company_name)
            ON DUPLICATE KEY UPDATE
                type = VALUES(type),
                name = VALUES(name),
                first_name = VALUES(first_name),
                company_name = VALUES(company_name)
        ');

        $stmt->execute([
            'id' => $person->id()->value(),
            'type' => $person->type()->value,
            'name' => $person->name(),
            'first_name' => $person->firstName(),
            'company_name' => $person->companyName(),
        ]);
    }

    public function findById(PersonId $id): ?Person
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM persons WHERE id = :id
        ');
        $stmt->execute(['id' => $id->value()]);
        
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    /** @return Person[] */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM persons ORDER BY name');
        $rows = $stmt->fetchAll();

        return array_map([$this, 'hydrate'], $rows);
    }

    public function delete(PersonId $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM persons WHERE id = :id');
        $stmt->execute(['id' => $id->value()]);
    }

    private function hydrate(array $row): Person
    {
        return new Person(
            new PersonId($row['id']),
            PersonType::from($row['type']),
            $row['name'],
            $row['first_name'],
            $row['company_name']
        );
    }
}
