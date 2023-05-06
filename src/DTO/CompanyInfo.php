<?php

declare(strict_types=1);

namespace App\DTO;

class CompanyInfo
{
    /**
     * @param array<int, BusinessLine> $businessLines
     */
    public function __construct(
        private readonly string $businessId,
        private readonly string $name,
        private readonly string $website,
        private readonly Address $currentAddress,
        private readonly array $businessLines
    ) {
    }

    public function getBusinessId(): string
    {
        return $this->businessId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getWebsite(): string
    {
        return $this->website;
    }

    public function getCurrentAddress(): Address
    {
        return $this->currentAddress;
    }

    public function getBusinessLines(): array
    {
        return $this->businessLines;
    }
}
