<?php

declare(strict_types=1);

namespace App\Tests\Integration\DTO;

use App\DTO\Address;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AddressTest extends KernelTestCase
{
    private Address $entity;
    private DateTimeImmutable $expectedRegistrationDate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->expectedRegistrationDate = new DateTimeImmutable();

        $this->entity = new Address('Cool street 5 B', 'Cool-City', '00111', $this->expectedRegistrationDate);
    }

    #[TestDox('Test that `getStreet()` method returns expected value')]
    public function testGetStreetMethodReturnsExpectedValue(): void
    {
        self::assertSame('Cool street 5 B', $this->entity->getStreet());
    }

    #[TestDox('Test that `getCity()` method returns expected value')]
    public function testGetCityMethodReturnsExpectedValue(): void
    {
        self::assertSame('Cool-City', $this->entity->getCity());
    }

    #[TestDox('Test that `getPostalCode()` method returns expected value')]
    public function testGetPostalCodeMethodReturnsExpectedValue(): void
    {
        self::assertSame('00111', $this->entity->getPostalCode());
    }

    #[TestDox('Test that `getRegistrationDate()` method returns expected value')]
    public function testGetRegistrationDateMethodReturnsExpectedValue(): void
    {
        self::assertSame($this->expectedRegistrationDate, $this->entity->getRegistrationDate());
    }
}
