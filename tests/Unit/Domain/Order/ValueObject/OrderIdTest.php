<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Order\ValueObject;

use App\Domain\Order\ValueObject\OrderId;
use PHPUnit\Framework\TestCase;

final class OrderIdTest extends TestCase
{
    public function testShouldCreateValidOrderId(): void
    {
        // Arrange & Act
        $orderId = new OrderId(1);

        // Assert
        $this->assertSame(1, $orderId->getValue());
    }

    public function testShouldThrowExceptionWhenIdIsNegative(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Order ID must be a non-negative integer');

        // Act
        new OrderId(-1);
    }

    public function testShouldReturnTrueWhenOrderIdsAreEqual(): void
    {
        // Arrange
        $orderId1 = new OrderId(1);
        $orderId2 = new OrderId(1);

        // Act & Assert
        $this->assertTrue($orderId1->equals($orderId2));
    }

    public function testShouldReturnFalseWhenOrderIdsAreDifferent(): void
    {
        // Arrange
        $orderId1 = new OrderId(1);
        $orderId2 = new OrderId(2);

        // Act & Assert
        $this->assertFalse($orderId1->equals($orderId2));
    }
}

