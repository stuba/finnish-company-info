# Finnish Company Info

With this PHP library you can easily fetch data on a company 
given its business ID (y-tunnus):

- Name of the company
- Website
- Current address (street, city, postal code)
- Current main line of business (code and textual description)

Library uses https://avoindata.prh.fi/ytj_en.html as the data source.

## Installation

You can require library with:

```
composer require rikukukkaniemi/finnish-company-info
```

See requirements from [Packagist](https://packagist.org/packages/rikukukkaniemi/finnish-company-info).

## How to use

Here is an example on how to use the library:

```
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

    $businessLines = $companyInfo->getBusinessLines();      // Array of BusinessLine objects (can be empty)
    $businessLines[0]->getCode();                           // String
    $businessLines[0]->getDescription();                    // String
    $businessLines[0]->getLanguage();                       // String (language of description)
}
```

Note that `CompanyInfoException` is the parent exception. You can also catch a more specific cases of exceptions:

- `InvalidBusinessIdException` when business ID seems to be invalid.
- `CompanyNotFoundException` when company is not found for given business ID.
- `UnexpectedClientDataException` when the data source returns unexpected data. Should not occur unless data source is broken.

The example code can also be found [here](https://github.com/RikuKukkaniemi/finnish-company-info/blob/master/Example/Example.php).