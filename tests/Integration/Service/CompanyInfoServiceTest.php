<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Exception\InvalidBusinessIdException;
use App\Service\CompanyInfoService;
use Generator;
use GuzzleHttp\Client;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CompanyInfoServiceTest extends KernelTestCase
{
    private CompanyInfoService $service;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->getMockBuilder(Client::class)->getMock();

        $this->service = new CompanyInfoService($this->client);
    }

    #[DataProvider('dataProviderInvalidBusinessId')]
    #[TestDox('Test that `getCompanyInformation()` method throws exception when business ID is invalid ($invalidBusinessId)')]
    public function testGetCompanyInformationMethodThrowsExceptionWhenBusinessIdIsInvalid(string $invalidBusinessId): void
    {
        self::expectException(InvalidBusinessIdException::class);

        $this->service->getCompanyInformation($invalidBusinessId);
    }

    public static function dataProviderInvalidBusinessId(): Generator
    {
        yield ['111-1'];        // Too short
        yield ['11111111-1'];   // Too long
        yield ['123456a-b'];    // Letters
        yield ['         '];    // Empty spaces
    }
}
