<?php

declare(strict_types=1);

namespace Example;

use RikuKukkaniemi\FinnishCompanyInfo\Exception\CompanyInfoException;
use RikuKukkaniemi\FinnishCompanyInfo\Service\CompanyInfoService;

class Example
{
    public function example(CompanyInfoService $companyInfoService): void
    {
        try {
            $companyInfo = $companyInfoService->getCompanyInformation('1234567-8');
        } catch (CompanyInfoException) {
            // Handle exception
            return;
        }

        $companyInfo->getName();                                // String
        $companyInfo->getWebsite();                             // String or null

        $currentAddress = $companyInfo->getCurrentAddress();    // Address object
        $currentAddress->getStreet();                           // String
        $currentAddress->getCity();                             // String
        $currentAddress->getPostalCode();                       // String

        $businessLines = $companyInfo->getBusinessLines();      // Array of BusinessLine objects
        $businessLines[0]->getCode();                           // String
        $businessLines[0]->getDescription();                    // String
        $businessLines[0]->getLanguage();                       // String (language of description)
    }
}
