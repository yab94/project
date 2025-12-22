<?php

declare(strict_types=1);

namespace App\CRM\Application;

use App\CRM\Application\Command\CreatePersonCommand;
use App\CRM\Domain\AggregateRoot\Person;
use App\CRM\Domain\Repository\PersonRepositoryInterface;
use App\CRM\Domain\ValueObject\PersonType;
use App\CRM\Domain\ValueObject\PersonId;

final class PersonService
{
    private PersonRepositoryInterface $personRepository;

    public function __construct(PersonRepositoryInterface $personRepository)
    {
        $this->personRepository = $personRepository;
    }

    public function createPerson(CreatePersonCommand $command): Person
    {
        $type = PersonType::from($command->type);
        
        if ($type === PersonType::INDIVIDUAL) {
            $names = explode(' ', $command->name, 2);
            $person = Person::createIndividual(
                $names[0] ?? '',
                $names[1] ?? ''
            );
        } else {
            $person = Person::createCompany($command->name);
        }

        $this->personRepository->save($person);

        return $person;
    }

    public function findById(string $id): ?Person
    {
        return $this->personRepository->findById(
            new PersonId($id)
        );
    }

    /** @return Person[] */
    public function findAll(): array
    {
        return $this->personRepository->findAll();
    }

    public function deletePerson(string $id): void
    {
        $this->personRepository->delete(
            new PersonId($id)
        );
    }
}
