<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Order\ValueObject;

use App\Domain\Order\ValueObject\ContractorType;
use PHPUnit\Framework\TestCase;

final class ContractorTypeTest extends TestCase
{
    public function testShouldCreateIndividualContractorType(): void
    {
        // Arrange & Act
        $contractorType = ContractorType::INDIVIDUAL;

        // Assert
        $this->assertSame(1, $contractorType->getValue());
        $this->assertTrue($contractorType->isIndividual());
        $this->assertFalse($contractorType->isLegalEntity());
    }

    public function testShouldCreateLegalEntityContractorType(): void
    {
        // Arrange & Act
        $contractorType = ContractorType::LEGAL_ENTITY;

        // Assert
        $this->assertSame(2, $contractorType->getValue());
        $this->assertFalse($contractorType->isIndividual());
        $this->assertTrue($contractorType->isLegalEntity());
    }

    public function testShouldCreateFromInt(): void
    {
        // Arrange & Act
        $contractorType = ContractorType::fromInt(1);

        // Assert
        $this->assertSame(ContractorType::INDIVIDUAL, $contractorType);
    }

    public function testShouldThrowExceptionWhenContractorTypeIsInvalid(): void
    {
        // Assert
        $this->expectException(\ValueError::class);

        // Act
        ContractorType::fromInt(99);
    }
}
