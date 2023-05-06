<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Address;
use App\DTO\BusinessLine;
use App\DTO\CompanyInfo;
use App\Exception\CompanyInfoException;
use App\Exception\CompanyNotFoundException;
use App\Exception\InvalidBusinessIdException;
use App\Exception\InvalidCompanyDataException;
use GuzzleHttp\Client;
use Throwable;

class CompanyInfoService
{
    public function __construct(private readonly Client $client)
    {
    }

    /**
     * @throws CompanyInfoException
     */
    public function getCompanyInformation(string $businessId): CompanyInfo
    {
        $this->validateBusinessId($businessId);

        $result = $this->getResponseData($businessId);

        try {
            return new CompanyInfo(
                $businessId,
                $result['name'],
                $this->getWebsite($result['contactDetails']),
                $this->getCurrentAddress($result['addresses']),
                $this->getBusinessLines($result['businessLines'])
            );
        } catch (Throwable) {
            throw new InvalidCompanyDataException("Company with business ID '$businessId' had insufficient data");
        }
    }

    /**
     * @throws InvalidBusinessIdException
     */
    private function validateBusinessId(string $businessId): void
    {
        if (!preg_match('/^[0-9]{6,7}-[0-9]$/', $businessId)) {
            throw new InvalidBusinessIdException("'$businessId' is not valid business ID.");
        }
    }

    /**
     * @throws CompanyNotFoundException
     */
    private function getResponseData(string $businessId): array
    {
        try {
            $response = $this->client->get('https://avoindata.prh.fi/bis/v1/' . $businessId);

            $decoded = json_decode($response->getBody()->getContents(), true);

            return $decoded['results'][0];
        } catch (Throwable) {
            throw new CompanyNotFoundException("Company not found for business ID '$businessId'.");
        }
    }

    private function getCurrentAddress(array $addresses): Address
    {
        $currentAddress = $this->sortByRegistrationDate($addresses)[0];

        return new Address(
            $currentAddress['street'],
            $currentAddress['city'],
            $currentAddress['postCode'],
        );
    }

    private function getWebsite(array $contactDetails): ?string
    {
        $website = null;

        foreach ($this->sortByRegistrationDate($contactDetails) as $contactDetail) {
            try {
                if ($this->isValidWebsite($contactDetail['value'])) {
                    $website = $contactDetail['value'];

                    break;
                }
            } catch (Throwable) {
                continue;
            }
        }

        return $website;
    }

    /**
     * @return array<int, BusinessLine>
     */
    private function getBusinessLines(array $businessLineData): array
    {
        $businessLines = [];

        foreach ($businessLineData as $businessLine) {
            try {
                $businessLines[] = new BusinessLine(
                    $businessLine['code'],
                    $businessLine['name'],
                    $businessLine['language']
                );
            } catch (Throwable) {
                continue;
            }
        }

        return $businessLines;
    }

    /**
     * The latest registered element is first element of array
     */
    private function sortByRegistrationDate(array $elements): array
    {
        usort($elements, function ($a, $b) {
            return strtotime($b['registrationDate']) - strtotime($a['registrationDate']);
        });

        return $elements;
    }

    private function isValidWebsite(string $value): bool
    {
        return (bool) preg_match(
            '/((http|https):\/\/)?[a-zA-Z0-9.\/?:@\-_=#]+\.([a-zA-Z0-9&.\/?:@\-_=#])*/',
            $value
        );
    }
}
