<?php

declare(strict_types=1);

namespace App\Tests\Integration\DTO;

use App\DTO\Address;
use App\DTO\BusinessLine;
use App\DTO\CompanyInfo;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CompanyInfoTest extends KernelTestCase
{
    private CompanyInfo $entity;
    private Address $expectedAddress;
    /**
     * @var array<int, BusinessLine>
     */
    private array $expectedBusinessLines;

    protected function setUp(): void
    {
        parent::setUp();

        $this->expectedAddress = $this->getMockBuilder(Address::class)->disableOriginalConstructor()->getMock();
        $this->expectedBusinessLines = [
            $this->getMockBuilder(BusinessLine::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(BusinessLine::class)->disableOriginalConstructor()->getMock(),
        ];

        $this->entity = new CompanyInfo(
            '1234567-8',
            'Company Name',
            'www.example.com',
            $this->expectedAddress,
            $this->expectedBusinessLines
        );
    }

    #[TestDox('Test that `getBusinessId()` method returns expected value')]
    public function testGetBusinessIdMethodReturnsExpectedValue(): void
    {
        self::assertSame('1234567-8', $this->entity->getBusinessId());
    }

    #[TestDox('Test that `getName()` method returns expected value')]
    public function testGetNameMethodReturnsExpectedValue(): void
    {
        self::assertSame('Company Name', $this->entity->getName());
    }

    #[TestDox('Test that `getWebsite()` method returns expected value')]
    public function testGetWebsiteMethodReturnsExpectedValue(): void
    {
        self::assertSame('www.example.com', $this->entity->getWebsite());
    }

    #[TestDox('Test that `getCurrentAddress()` method returns expected value')]
    public function testGetCurrentAddressMethodReturnsExpectedValue(): void
    {
        self::assertSame($this->expectedAddress, $this->entity->getCurrentAddress());
    }

    #[TestDox('Test that `getBusinessLines()` method returns expected value')]
    public function testGetBusinessLinesMethodReturnsExpectedValue(): void
    {
        self::assertSame($this->expectedBusinessLines, $this->entity->getBusinessLines());
    }
}
