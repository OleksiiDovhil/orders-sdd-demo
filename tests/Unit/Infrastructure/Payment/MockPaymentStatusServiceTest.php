<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Payment;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Entity\OrderItem;
use App\Domain\Order\ValueObject\ContractorType;
use App\Domain\Order\ValueObject\OrderId;
use App\Domain\Order\ValueObject\OrderNumber;
use App\Domain\Order\ValueObject\UniqueOrderNumber;
use App\Infrastructure\Payment\MockPaymentStatusService;
use PHPUnit\Framework\TestCase;

final class MockPaymentStatusServiceTest extends TestCase
{
    private MockPaymentStatusService $service;

    protected function setUp(): void
    {
        $this->service = new MockPaymentStatusService();
    }

    private function createOrder(ContractorType $contractorType, bool $isPaid = false, int $orderId = 1): Order
    {
        return new Order(
            new OrderId($orderId),
            new OrderNumber(1),
            new UniqueOrderNumber('2025-11-1'),
            1000,
            $contractorType,
            new \DateTimeImmutable(),
            $isPaid,
            new OrderItem(1, 1000, 1)
        );
    }

    public function testShouldReturnDatabaseStatusForIndividualOrder(): void
    {
        // Arrange
        $unpaidIndividualOrder = $this->createOrder(ContractorType::INDIVIDUAL, false);
        $paidIndividualOrder = $this->createOrder(ContractorType::INDIVIDUAL, true);

        // Act
        $unpaidResult = $this->service->checkPaymentStatus($unpaidIndividualOrder);
        $paidResult = $this->service->checkPaymentStatus($paidIndividualOrder);

        // Assert - For individual orders, service respects database state
        $this->assertFalse($unpaidResult, 'Should return false for unpaid individual order');
        $this->assertTrue($paidResult, 'Should return true for paid individual order');
    }

    public function testShouldReturnRandomValueForLegalEntityOrder(): void
    {
        // Arrange
        $legalEntityOrder = $this->createOrder(ContractorType::LEGAL_ENTITY);

        // Act - Run multiple times to verify randomness
        $results = [];
        for ($i = 0; $i < 20; $i++) {
            $results[] = $this->service->checkPaymentStatus($legalEntityOrder);
        }

        // Assert - At least one true and one false should appear (very likely with 20 runs)
        $this->assertContains(true, $results, 'Should return true at least once');
        $this->assertContains(false, $results, 'Should return false at least once');
    }
}
