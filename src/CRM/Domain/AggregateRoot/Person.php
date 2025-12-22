<?php

declare(strict_types=1);

namespace App\CRM\Domain\AggregateRoot;

use App\Core\Domain\AggregateRoot;
use App\CRM\Domain\ValueObject\PersonId;
use App\CRM\Domain\ValueObject\PersonType;
use App\CRM\Domain\ValueObject\Address;
use App\CRM\Domain\ValueObject\Contact;

/**
 * Person Aggregate Root
 * 
 * Manages the Person aggregate which includes:
 * - Address entities (multiple addresses per person)
 * - Contact entities (email, phone, etc.)
 * 
 * All modifications to addresses and contacts must go through Person
 * to maintain aggregate consistency.
 */
final class Person implements AggregateRoot
{
    private PersonId $id;
    private PersonType $type;
    private string $name;
    private ?string $firstName;
    private ?string $companyName;
    /** @var Address[] */
    private array $addresses = [];
    /** @var Contact[] */
    private array $contacts = [];

    public function __construct(
        PersonId $id,
        PersonType $type,
        string $name,
        ?string $firstName = null,
        ?string $companyName = null
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->setName($name);
        $this->firstName = $firstName;
        $this->companyName = $companyName;

        $this->validate();
    }

    public static function createIndividual(string $name, string $firstName): self
    {
        return new self(
            PersonId::generate(),
            PersonType::INDIVIDUAL,
            $name,
            $firstName
        );
    }

    public static function createCompany(string $companyName): self
    {
        return new self(
            PersonId::generate(),
            PersonType::COMPANY,
            $companyName,
            null,
            $companyName
        );
    }

    public function id(): PersonId
    {
        return $this->id;
    }

    public function type(): PersonType
    {
        return $this->type;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function firstName(): ?string
    {
        return $this->firstName;
    }

    public function companyName(): ?string
    {
        return $this->companyName;
    }

    public function fullName(): string
    {
        if ($this->type === PersonType::COMPANY) {
            return $this->companyName ?? $this->name;
        }
        return trim($this->firstName . ' ' . $this->name);
    }

    /** @return Address[] */
    public function addresses(): array
    {
        return $this->addresses;
    }

    /** @return Contact[] */
    public function contacts(): array
    {
        return $this->contacts;
    }

    public function addAddress(Address $address): void
    {
        $this->addresses[] = $address;
    }

    public function removeAddress(Address $address): void
    {
        $this->addresses = array_filter(
            $this->addresses,
            fn(Address $a) => !$a->id()->equals($address->id())
        );
        $this->addresses = array_values($this->addresses);
    }

    public function addContact(Contact $contact): void
    {
        $this->contacts[] = $contact;
    }

    public function removeContact(Contact $contact): void
    {
        $this->contacts = array_filter(
            $this->contacts,
            fn(Contact $c) => !$c->id()->equals($contact->id())
        );
        $this->contacts = array_values($this->contacts);
    }

    public function updateName(string $name): void
    {
        $this->setName($name);
    }

    public function updateFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function updateCompanyName(?string $companyName): void
    {
        $this->companyName = $companyName;
    }

    private function setName(string $name): void
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('Name cannot be empty');
        }
        $this->name = $name;
    }

    private function validate(): void
    {
        if ($this->type === PersonType::INDIVIDUAL && empty($this->firstName)) {
            throw new \InvalidArgumentException('Individual person must have a first name');
        }
        if ($this->type === PersonType::COMPANY && empty($this->companyName)) {
            throw new \InvalidArgumentException('Company must have a company name');
        }
    }
}
