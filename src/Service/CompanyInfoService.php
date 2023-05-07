<?php

declare(strict_types=1);

namespace RikuKukkaniemi\FinnishCompanyInfo\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RikuKukkaniemi\FinnishCompanyInfo\DTO\Address;
use RikuKukkaniemi\FinnishCompanyInfo\DTO\BusinessLine;
use RikuKukkaniemi\FinnishCompanyInfo\DTO\CompanyInfo;
use RikuKukkaniemi\FinnishCompanyInfo\Exception\CompanyInfoException;
use RikuKukkaniemi\FinnishCompanyInfo\Exception\CompanyNotFoundException;
use RikuKukkaniemi\FinnishCompanyInfo\Exception\InvalidBusinessIdException;
use RikuKukkaniemi\FinnishCompanyInfo\Exception\UnexpectedClientDataException;
use Throwable;
use TypeError;

class CompanyInfoService
{
    public function __construct(private readonly Client $client)
    {
    }

    /**
     * Get Finnish company information using business ID (y-tunnus).
     * For more information and examples see project README.
     *
     * @see https://github.com/RikuKukkaniemi/finnish-company-info#finnish-company-info
     * @throws CompanyInfoException
     */
    public function getCompanyInformation(string $businessId): CompanyInfo
    {
        $this->validateBusinessId($businessId);

        $clientData = $this->getClientData($businessId);

        try {
            return new CompanyInfo(
                $businessId,
                $clientData['name'],
                $this->getWebsite($clientData['contactDetails']),
                $this->getCurrentAddress($clientData['addresses']),
                $this->getBusinessLines($clientData['businessLines'])
            );
        } catch (Throwable) {
            throw new UnexpectedClientDataException("Got unexpected Client data for business ID '$businessId'.");
        }
    }

    /**
     * A light validation on business ID. Only validates that string contains
     * numbers with hyphen before the check number.
     *
     * @throws InvalidBusinessIdException
     */
    private function validateBusinessId(string $businessId): void
    {
        if (!preg_match('/^[0-9]{6,7}-[0-9]$/', $businessId)) {
            throw new InvalidBusinessIdException("'$businessId' is not valid business ID.");
        }
    }

    /**
     * Returns the data from https://avoindata.prh.fi/ytj_en.html.
     *
     * @throws CompanyNotFoundException|UnexpectedClientDataException
     */
    private function getClientData(string $businessId): array
    {
        try {
            $response = $this->client->get('https://avoindata.prh.fi/bis/v1/' . $businessId);
        } catch (GuzzleException) {
            throw new CompanyNotFoundException("Company not found for business ID '$businessId'.");
        }

        try {
            $decoded = json_decode($response->getBody()->__toString(), true);

            return $decoded['results'][0];
        } catch (Throwable) {
            throw new UnexpectedClientDataException("Got unexpected Client data for business ID '$businessId'.");
        }
    }

    /**
     * Returns the current address. Expects to have at least one address as
     * companies should have, otherwise throws TypeError.
     *
     * @throws TypeError
     */
    private function getCurrentAddress(array $addresses): Address
    {
        $currentAddress = $this->sortByRegistrationDate($addresses)[0];

        return new Address(
            $currentAddress['street'],
            $currentAddress['city'],
            $currentAddress['postCode'],
        );
    }

    /**
     * Tries to parse most recent website from contact details.
     * If valid website is not found, null is returned.
     */
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
     * Parses business lines from array. Accepts business line only if it has
     * code, name (description) and language data values.
     *
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
     * Sorts the elements in a way that the latest registered
     * element is first element of array.
     *
     * @throws TypeError If $elements is not multidimensional array
     */
    private function sortByRegistrationDate(array $elements): array
    {
        usort($elements, function (array $a, array $b) {
            return strtotime($b['registrationDate']) - strtotime($a['registrationDate']);
        });

        return $elements;
    }

    /**
     * Checks if value is valid website. Following websites should be validated as valid:
     * http://example.com
     * https://example.com
     * www.example.com
     * example.com
     */
    private function isValidWebsite(string $value): bool
    {
        return (bool) preg_match(
            '/((http|https):\/\/)?[a-zA-Z0-9.\/?:@\-_=#]+\.([a-zA-Z0-9&.\/?:@\-_=#])*/',
            $value
        );
    }
}
