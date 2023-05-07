<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\DTO\CompanyInfo;
use App\Exception\CompanyNotFoundException;
use App\Exception\InvalidBusinessIdException;
use App\Exception\UnexpectedClientDataException;
use App\Service\CompanyInfoService;
use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
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

        $this->callGetCompanyInformation($invalidBusinessId);
    }

    #[TestDox('Test that `getCompanyInformation()` method throws exception when Client throws exception')]
    public function testGetCompanyInformationMethodThrowsExceptionWhenClientThrowsException(): void
    {
        $businessId = '1234567-8';

        $this->client
            ->expects(self::once())
            ->method('get')
            ->with('https://avoindata.prh.fi/bis/v1/' . $businessId)
            ->willThrowException(
                $this->getMockBuilder(ClientException::class)->disableOriginalConstructor()->getMock()
            );

        self::expectException(CompanyNotFoundException::class);

        $this->callGetCompanyInformation($businessId);
    }

    #[TestDox('Test that `getCompanyInformation()` method throws exception when Client response does not have results')]
    public function testGetCompanyInformationMethodThrowsExceptionWhenClientResponseDoesNotHaveResults(): void
    {
        $businessId = '1234567-8';

        $this->client
            ->expects(self::once())
            ->method('get')
            ->with('https://avoindata.prh.fi/bis/v1/' . $businessId)
            ->willReturn(new Response(body: json_encode(['foo' => 'bar'])));

        self::expectException(UnexpectedClientDataException::class);

        $this->callGetCompanyInformation($businessId);
    }

    public static function dataProviderInvalidBusinessId(): Generator
    {
        yield ['111-1'];        // Too short
        yield ['11111111-1'];   // Too long
        yield ['123456a-b'];    // Letters
        yield ['         '];    // Empty spaces
    }

    private function setUpClientGetCall(string $businessId = '1234567-8'): void
    {
        $this->client
            ->expects(self::once())
            ->method('get')
            ->with('https://avoindata.prh.fi/bis/v1/' . $businessId);
    }

    private function callGetCompanyInformation(string $businessId = '1234567-8'): CompanyInfo
    {
        return $this->service->getCompanyInformation($businessId);
    }
}
