<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Order\Entity;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Entity\OrderItem;
use App\Domain\Order\ValueObject\ContractorType;
use App\Domain\Order\ValueObject\OrderId;
use App\Domain\Order\ValueObject\OrderNumber;
use App\Domain\Order\ValueObject\UniqueOrderNumber;
use PHPUnit\Framework\TestCase;

final class OrderTest extends TestCase
{
    public function testShouldCreateOrderWithValidData(): void
    {
        // Arrange
        $orderId = new OrderId(1);
        $orderNumber = new OrderNumber(12345);
        $uniqueOrderNumber = new UniqueOrderNumber('2025-01-12345');
        $contractorType = ContractorType::INDIVIDUAL;
        $item = new OrderItem(1, 1000, 2);

        // Act
        $order = new Order(
            $orderId,
            $orderNumber,
            $uniqueOrderNumber,
            2000, // sum (independent money field)
            $contractorType,
            false, // isPaid
            $item
        );

        // Assert
        $this->assertSame($orderId, $order->getId());
        $this->assertSame($orderNumber, $order->getOrderNumber());
        $this->assertSame($uniqueOrderNumber, $order->getUniqueOrderNumber());
        $this->assertSame(2000, $order->getSum());
        $this->assertSame($contractorType, $order->getContractorType());
        $this->assertCount(1, $order->getItems());
    }

    public function testShouldThrowExceptionWhenSumIsNegative(): void
    {
        // Arrange
        $orderId = new OrderId(1);
        $orderNumber = new OrderNumber(12345);
        $uniqueOrderNumber = new UniqueOrderNumber('2025-01-12345');
        $contractorType = ContractorType::INDIVIDUAL;

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Order sum cannot be negative');

        // Act
        new Order(
            $orderId,
            $orderNumber,
            $uniqueOrderNumber,
            -100,
            $contractorType,
            false // isPaid
        );
    }
}
