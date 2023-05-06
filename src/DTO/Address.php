<?php

declare(strict_types=1);

namespace App\DTO;

use DateTimeImmutable;

class Address
{
    public function __construct(
        private readonly string $street,
        private readonly string $city,
        private readonly string $postalCode,
        private readonly DateTimeImmutable $registrationDate
    ) {
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getRegistrationDate(): DateTimeImmutable
    {
        return $this->registrationDate;
    }
}
