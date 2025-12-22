<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\CRM\Domain\AggregateRoot\Person;
use App\CRM\Domain\ValueObject\PersonType;

final class PersonTest extends TestCase
{
    public function testCreateIndividual(): void
    {
        $person = Person::createIndividual('Doe', 'John');

        $this->assertEquals(PersonType::INDIVIDUAL, $person->type());
        $this->assertEquals('Doe', $person->name());
        $this->assertEquals('John', $person->firstName());
        $this->assertNull($person->companyName());
    }

    public function testCreateCompany(): void
    {
        $person = Person::createCompany('Acme Corp');

        $this->assertEquals(PersonType::COMPANY, $person->type());
        $this->assertEquals('Acme Corp', $person->name());
        $this->assertNull($person->firstName());
        $this->assertEquals('Acme Corp', $person->companyName());
    }

    public function testFullName(): void
    {
        $individual = Person::createIndividual('Doe', 'John');
        $this->assertEquals('John Doe', $individual->fullName());

        $company = Person::createCompany('Acme Corp');
        $this->assertEquals('Acme Corp', $company->fullName());
    }
}
