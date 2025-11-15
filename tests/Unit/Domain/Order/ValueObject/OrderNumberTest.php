<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Order\ValueObject;

use App\Domain\Order\ValueObject\OrderNumber;
use PHPUnit\Framework\TestCase;

final class OrderNumberTest extends TestCase
{
    public function testShouldCreateValidOrderNumber(): void
    {
        // Arrange & Act
        $orderNumber = new OrderNumber(12345);

        // Assert
        $this->assertSame(12345, $orderNumber->getValue());
    }

    public function testShouldThrowExceptionWhenValueIsZero(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Order number must be a positive integer');

        // Act
        new OrderNumber(0);
    }

    public function testShouldThrowExceptionWhenValueIsNegative(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Order number must be a positive integer');

        // Act
        new OrderNumber(-1);
    }
}
