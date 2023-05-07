<?php

declare(strict_types=1);

namespace RikuKukkaniemi\FinnishCompanyInfo\Tests\Integration\Service;

use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use RikuKukkaniemi\FinnishCompanyInfo\DTO\CompanyInfo;
use RikuKukkaniemi\FinnishCompanyInfo\Exception\CompanyNotFoundException;
use RikuKukkaniemi\FinnishCompanyInfo\Exception\InvalidBusinessIdException;
use RikuKukkaniemi\FinnishCompanyInfo\Exception\UnexpectedClientDataException;
use RikuKukkaniemi\FinnishCompanyInfo\Service\CompanyInfoService;
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
        $this->client
            ->expects(self::once())
            ->method('get')
            ->with('https://avoindata.prh.fi/bis/v1/1234567-8')
            ->willThrowException(
                $this->getMockBuilder(ClientException::class)->disableOriginalConstructor()->getMock()
            );

        self::expectException(CompanyNotFoundException::class);

        $this->callGetCompanyInformation();
    }

    #[DataProvider('dataProviderUnexpectedClientData')]
    #[TestDox('Test that `getCompanyInformation()` method throws exception when Client returns unexpected data')]
    public function testGetCompanyInformationMethodThrowsExceptionWhenClientReturnsUnexpectedData(
        array $unexpectedClientData
    ): void {
        $this->setUpClientGetCall($unexpectedClientData);

        self::expectException(UnexpectedClientDataException::class);

        $this->callGetCompanyInformation();
    }

    #[TestDox('Test that `getCompanyInformation()` method returns expected CompanyInfo data when Client data is valid')]
    public function testGetCompanyInformationMethodReturnsExpectedCompanyInfoDataWhenClientDataIsValid(): void
    {
        $this->setUpClientGetCall(self::getValidClientResponseData());

        $companyInfo = $this->callGetCompanyInformation();

        self::assertSame('1234567-8', $companyInfo->getBusinessId());
        self::assertSame('Example Company', $companyInfo->getName());
        self::assertSame('www.example.com', $companyInfo->getWebsite());
        self::assertCount(2, $companyInfo->getBusinessLines());

        $address = $companyInfo->getCurrentAddress();

        self::assertSame('Example Street 123', $address->getStreet());
        self::assertSame('Example City', $address->getCity());
        self::assertSame('12345', $address->getPostalCode());
    }

    #[DataProvider('dataProviderValidWebsite')]
    #[TestDox('Test that `getCompanyInformation()` method returns expected CompanyInfo website ($website) when Client data is valid')]
    public function testGetCompanyInformationMethodReturnsExpectedCompanyInfoWebsiteWhenClientDataIsValid(
        array $validClientData,
        ?string $website
    ): void {
        $this->setUpClientGetCall($validClientData);

        self::assertSame($website, $this->callGetCompanyInformation()->getWebsite());
    }

    public static function dataProviderInvalidBusinessId(): Generator
    {
        yield ['111-1'];        // Too short
        yield ['11111111-1'];   // Too long
        yield ['123456a-b'];    // Letters
        yield ['         '];    // Empty spaces
    }

    public static function dataProviderUnexpectedClientData(): Generator
    {
        yield [['foo' => 'bar']];

        $invalidCompanyName = self::getValidClientResponseData();
        $invalidCompanyName['results'][0]['name'] = ['expecting' => 'string'];
        yield [$invalidCompanyName];

        $noAddresses = self::getValidClientResponseData();
        $noAddresses['results'][0]['addresses'] = [];
        yield [$noAddresses];

        $invalidAddressData = self::getValidClientResponseData();
        $invalidAddressData['results'][0]['addresses'] = 'expecting array';
        yield [$invalidAddressData];

        $invalidContactDetailsData = self::getValidClientResponseData();
        $invalidContactDetailsData['results'][0]['contactDetails'] = 'expecting array';
        yield [$invalidContactDetailsData];

        $invalidBusinessLinesData = self::getValidClientResponseData();
        $invalidBusinessLinesData['results'][0]['businessLines'] = 'expecting array';
        yield [$invalidBusinessLinesData];
    }

    public static function dataProviderValidWebsite(): Generator
    {
        $validClientData = self::getValidClientResponseData();

        $validClientData['results'][0]['contactDetails'] = ['insufficient' => 'data'];
        yield [$validClientData, null];

        $validClientData['results'][0]['contactDetails'] = self::getValidContactDetailsElement('http://example.com');
        yield [$validClientData, 'http://example.com'];

        $validClientData['results'][0]['contactDetails'] = self::getValidContactDetailsElement('https://example.com');
        yield [$validClientData, 'https://example.com'];

        $validClientData['results'][0]['contactDetails'] = self::getValidContactDetailsElement('www.example.com');
        yield [$validClientData, 'www.example.com'];

        $validClientData['results'][0]['contactDetails'] = self::getValidContactDetailsElement('example.com');
        yield [$validClientData, 'example.com'];
    }

    private function setUpClientGetCall(array $clientData, string $businessId = '1234567-8'): void
    {
        $this->client
            ->expects(self::once())
            ->method('get')
            ->with('https://avoindata.prh.fi/bis/v1/' . $businessId)
            ->willReturn(new Response(body: json_encode($clientData)));
    }

    private function callGetCompanyInformation(string $businessId = '1234567-8'): CompanyInfo
    {
        return $this->service->getCompanyInformation($businessId);
    }

    private static function getValidClientResponseData(): array
    {
        return [
            'results' =>
                [[
                    "name" => 'Example Company',
                    "addresses" =>
                        [
                            [
                                "registrationDate" => "2023-04-04T18:48:31.941Z",
                                "endDate" => "2023-05-04T18:48:31.941Z",
                                "careOf" => "string",
                                "street" => "Not most recent street 2",
                                "postCode" => "string",
                                "city" => "string",
                                "language" => "string",
                            ],
                            [
                                "registrationDate" => "2023-05-04T18:48:31.941Z",
                                "endDate" => "2023-05-04T18:48:31.941Z",
                                "careOf" => "string",
                                "street" => "Example Street 123",
                                "postCode" => "12345",
                                "city" => "Example City",
                                "language" => "string",
                            ],
                        ],
                    "contactDetails" =>
                        [
                            [
                                "registrationDate" => "2023-04-04T18:48:31.941Z",
                                "endDate" => "2023-05-04T18:48:31.941Z",
                                "language" => "string",
                                "value" => "example.com",
                                "type" => "string"
                            ],
                            [
                                "registrationDate" => "2023-05-04T18:48:31.941Z",
                                "endDate" => "2023-05-04T18:48:31.941Z",
                                "language" => "string",
                                "value" => "www.example.com",
                                "type" => "string"
                            ],
                            [
                                "registrationDate" => "2023-05-04T18:48:31.941Z",
                                "endDate" => "2023-05-04T18:48:31.941Z",
                                "language" => "string",
                                "value" => "string",
                                "type" => "string"
                            ]
                        ],
                    "businessLines" =>
                        [
                            [
                                "registrationDate" => "2023-05-04T18:48:31.941Z",
                                "endDate" => "2023-05-04T18:48:31.941Z",
                                "code" => "string",
                                "name" => "string",
                                "language" => "string"
                            ],
                            [
                                "registrationDate" => "2023-05-04T18:48:31.941Z",
                                "endDate" => "2023-05-04T18:48:31.941Z",
                                "code" => "string",
                                "name" => "string",
                                "language" => "string"
                            ]
                        ]
                    ]]
                ];
    }

    private static function getValidContactDetailsElement(string $websiteValue): array
    {
        return [[
            "registrationDate" => "2023-04-04T18:48:31.941Z",
            "endDate" => "2023-05-04T18:48:31.941Z",
            "language" => "string",
            "value" => $websiteValue,
            "type" => "string"
        ]];
    }
}
