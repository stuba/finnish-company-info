<?php

declare(strict_types=1);

namespace App\Tests\Integration\DTO;

use App\DTO\BusinessLine;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BusinessLineTest extends KernelTestCase
{
    private BusinessLine $entity;
    protected function setUp(): void
    {
        parent::setUp();

        $this->entity = new BusinessLine('012345', 'Cool description', 'FI');
    }

    #[TestDox('Test that `getCode()` method returns expected value')]
    public function testGetCodeMethodReturnsExpectedValue(): void
    {
        self::assertSame('012345', $this->entity->getCode());
    }

    #[TestDox('Test that `getDescription()` method returns expected value')]
    public function testGetDescriptionMethodReturnsExpectedValue(): void
    {
        self::assertSame('Cool description', $this->entity->getDescription());
    }

    #[TestDox('Test that `getLanguage()` method returns expected value')]
    public function testGetLanguageMethodReturnsExpectedValue(): void
    {
        self::assertSame('FI', $this->entity->getLanguage());
    }
}
