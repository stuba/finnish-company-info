<?php

declare(strict_types=1);

namespace App\Tests\Integration\DTO;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use RikuKukkaniemi\FinnishCompanyInfo\DTO\Address;
use RikuKukkaniemi\FinnishCompanyInfo\DTO\BusinessLine;
use RikuKukkaniemi\FinnishCompanyInfo\DTO\CompanyInfo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CompanyInfoTest extends KernelTestCase
{
    #[TestDox('Test that `getBusinessId()` method returns expected value')]
    public function testGetBusinessIdMethodReturnsExpectedValue(): void
    {
        self::assertSame('1234567-8', $this->createDto()->getBusinessId());
    }

    #[TestDox('Test that `getName()` method returns expected value')]
    public function testGetNameMethodReturnsExpectedValue(): void
    {
        self::assertSame('Company Name', $this->createDto()->getName());
    }

    #[DataProvider('dataProviderWebsiteValues')]
    #[TestDox('Test that `getWebsite()` method returns expected value ($value)')]
    public function testGetWebsiteMethodReturnsExpectedValue(?string $value): void
    {
        self::assertSame($value, $this->createDto(website: $value)->getWebsite());
    }

    #[TestDox('Test that `getCurrentAddress()` method returns expected value')]
    public function testGetCurrentAddressMethodReturnsExpectedValue(): void
    {
        $expectedAddress = $this->createAddressMock();

        self::assertSame($expectedAddress, $this->createDto(address: $expectedAddress)->getCurrentAddress());
    }

    #[TestDox('Test that `getBusinessLines()` method returns expected value')]
    public function testGetBusinessLinesMethodReturnsExpectedValue(): void
    {
        $expectedBusinessLines = [
            $this->createBusinessLineMock(),
            $this->createBusinessLineMock()
        ];

        self::assertSame(
            $expectedBusinessLines,
            $this->createDto(businessLines: $expectedBusinessLines)->getBusinessLines()
        );
    }

    public static function dataProviderWebsiteValues(): Generator
    {
        yield ['www.example.com'];
        yield [null];
    }

    private function createAddressMock(): Address
    {
        return $this->getMockBuilder(Address::class)->disableOriginalConstructor()->getMock();
    }

    private function createBusinessLineMock(): BusinessLine
    {
        return $this->getMockBuilder(BusinessLine::class)->disableOriginalConstructor()->getMock();
    }

    private function createDto(
        ?string $website = 'www.example.com',
        ?Address $address = null,
        array $businessLines = []
    ): CompanyInfo {
        return new CompanyInfo(
            '1234567-8',
            'Company Name',
            $website,
            $address ?? $this->createAddressMock(),
            $businessLines
        );
    }
}
