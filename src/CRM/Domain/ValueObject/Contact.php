<?php

declare(strict_types=1);

namespace App\CRM\Domain\ValueObject;

use App\CRM\Domain\ValueObject\ContactId;
use App\CRM\Domain\ValueObject\Email;
use App\CRM\Domain\ValueObject\Phone;
use App\Core\Domain\ValueObject;

final class Contact implements ValueObject
{
    private ContactId $id;
    private ?Email $email;
    private ?Phone $phone;
    private string $type;

    public function __construct(
        ContactId $id,
        ?Email $email = null,
        ?Phone $phone = null,
        string $type = 'primary'
    ) {
        if ($email === null && $phone === null) {
            throw new \InvalidArgumentException('Contact must have at least email or phone');
        }
        $this->id = $id;
        $this->email = $email;
        $this->phone = $phone;
        $this->type = $type;
    }

    public static function createWithEmail(string $email, string $type = 'primary'): self
    {
        return new self(
            ContactId::generate(),
            new Email($email),
            null,
            $type
        );
    }

    public static function createWithPhone(string $phone, string $type = 'primary'): self
    {
        return new self(
            ContactId::generate(),
            null,
            new Phone($phone),
            $type
        );
    }

    public static function create(string $email, string $phone, string $type = 'primary'): self
    {
        return new self(
            ContactId::generate(),
            new Email($email),
            new Phone($phone),
            $type
        );
    }

    public function id(): ContactId
    {
        return $this->id;
    }

    public function email(): ?Email
    {
        return $this->email;
    }

    public function phone(): ?Phone
    {
        return $this->phone;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function updateEmail(?string $email): void
    {
        $this->email = $email ? new Email($email) : null;
    }

    public function updatePhone(?string $phone): void
    {
        $this->phone = $phone ? new Phone($phone) : null;
    }

    public function updateType(string $type): void
    {
        $this->type = $type;
    }

    public function equals(ValueObject $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        $emailEquals = ($this->email === null && $other->email === null)
            || ($this->email !== null && $other->email !== null && $this->email->equals($other->email));

        $phoneEquals = ($this->phone === null && $other->phone === null)
            || ($this->phone !== null && $other->phone !== null && $this->phone->equals($other->phone));

        return $this->id->equals($other->id)
            && $emailEquals
            && $phoneEquals
            && $this->type === $other->type;
    }
}
