<?php

declare(strict_types=1);

namespace App\CRM\Domain\ValueObject;

use App\CRM\Domain\ValueObject\AddressId;
use App\Core\Domain\ValueObject;

final class Address implements ValueObject
{
    private AddressId $id;
    private string $street;
    private string $postalCode;
    private string $city;
    private string $country;

    public function __construct(
        AddressId $id,
        string $street,
        string $postalCode,
        string $city,
        string $country = 'France'
    ) {
        $this->id = $id;
        $this->setStreet($street);
        $this->setPostalCode($postalCode);
        $this->setCity($city);
        $this->country = $country;
    }

    public static function create(
        string $street,
        string $postalCode,
        string $city,
        string $country = 'France'
    ): self {
        return new self(
            AddressId::generate(),
            $street,
            $postalCode,
            $city,
            $country
        );
    }

    public function id(): AddressId
    {
        return $this->id;
    }

    public function street(): string
    {
        return $this->street;
    }

    public function postalCode(): string
    {
        return $this->postalCode;
    }

    public function city(): string
    {
        return $this->city;
    }

    public function country(): string
    {
        return $this->country;
    }

    public function updateAddress(string $street, string $postalCode, string $city, string $country): void
    {
        $this->setStreet($street);
        $this->setPostalCode($postalCode);
        $this->setCity($city);
        $this->country = $country;
    }

    private function setStreet(string $street): void
    {
        if (empty(trim($street))) {
            throw new \InvalidArgumentException('Street cannot be empty');
        }
        $this->street = $street;
    }

    private function setPostalCode(string $postalCode): void
    {
        if (empty(trim($postalCode))) {
            throw new \InvalidArgumentException('Postal code cannot be empty');
        }
        $this->postalCode = $postalCode;
    }

    private function setCity(string $city): void
    {
        if (empty(trim($city))) {
            throw new \InvalidArgumentException('City cannot be empty');
        }
        $this->city = $city;
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->id->equals($other->id)
            && $this->street === $other->street
            && $this->postalCode === $other->postalCode
            && $this->city === $other->city
            && $this->country === $other->country;
    }
}
