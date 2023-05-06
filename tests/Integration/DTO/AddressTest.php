<?php

declare(strict_types=1);

namespace App\Tests\Integration\DTO;

use App\DTO\Address;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AddressTest extends KernelTestCase
{
    private Address $dto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dto = new Address('Cool street 5 B', 'Cool-City', '00111');
    }

    #[TestDox('Test that `getStreet()` method returns expected value')]
    public function testGetStreetMethodReturnsExpectedValue(): void
    {
        self::assertSame('Cool street 5 B', $this->dto->getStreet());
    }

    #[TestDox('Test that `getCity()` method returns expected value')]
    public function testGetCityMethodReturnsExpectedValue(): void
    {
        self::assertSame('Cool-City', $this->dto->getCity());
    }

    #[TestDox('Test that `getPostalCode()` method returns expected value')]
    public function testGetPostalCodeMethodReturnsExpectedValue(): void
    {
        self::assertSame('00111', $this->dto->getPostalCode());
    }
}
