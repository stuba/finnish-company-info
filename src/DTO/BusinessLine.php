<?php

declare(strict_types=1);

namespace RikuKukkaniemi\FinnishCompanyInfo\DTO;

class BusinessLine
{
    public function __construct(
        private readonly string $code,
        private readonly string $description,
        private readonly string $language
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }
}
