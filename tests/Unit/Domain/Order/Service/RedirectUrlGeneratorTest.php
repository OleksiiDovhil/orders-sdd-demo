<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Order\Service;

use App\Domain\Order\Service\RedirectUrlGenerator;
use App\Domain\Order\ValueObject\ContractorType;
use App\Domain\Order\ValueObject\UniqueOrderNumber;
use PHPUnit\Framework\TestCase;

final class RedirectUrlGeneratorTest extends TestCase
{
    public function testShouldGenerateIndividualRedirectUrl(): void
    {
        // Arrange
        $baseUrl = 'http://some-pay-agregator.com';
        $generator = new RedirectUrlGenerator($baseUrl);
        $uniqueOrderNumber = new UniqueOrderNumber('2025-01-12345');
        $contractorType = ContractorType::INDIVIDUAL;

        // Act
        $url = $generator->generate($uniqueOrderNumber, $contractorType);

        // Assert
        $this->assertSame('http://some-pay-agregator.com/pay/2025-01-12345', $url);
    }

    public function testShouldGenerateLegalEntityRedirectUrl(): void
    {
        // Arrange
        $baseUrl = 'http://some-pay-agregator.com';
        $generator = new RedirectUrlGenerator($baseUrl);
        $uniqueOrderNumber = new UniqueOrderNumber('2025-01-12345');
        $contractorType = ContractorType::LEGAL_ENTITY;

        // Act
        $url = $generator->generate($uniqueOrderNumber, $contractorType);

        // Assert
        $this->assertSame('http://some-pay-agregator.com/orders/2025-01-12345/bill', $url);
    }

    public function testShouldTrimTrailingSlashFromBaseUrl(): void
    {
        // Arrange
        $baseUrl = 'http://some-pay-agregator.com/';
        $generator = new RedirectUrlGenerator($baseUrl);
        $uniqueOrderNumber = new UniqueOrderNumber('2025-01-12345');
        $contractorType = ContractorType::INDIVIDUAL;

        // Act
        $url = $generator->generate($uniqueOrderNumber, $contractorType);

        // Assert
        $this->assertSame('http://some-pay-agregator.com/pay/2025-01-12345', $url);
    }
}
