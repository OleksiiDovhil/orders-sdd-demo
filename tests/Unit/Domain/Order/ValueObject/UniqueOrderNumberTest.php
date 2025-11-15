<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Order\ValueObject;

use App\Domain\Order\ValueObject\UniqueOrderNumber;
use PHPUnit\Framework\TestCase;

final class UniqueOrderNumberTest extends TestCase
{
    public function testShouldCreateValidUniqueOrderNumber(): void
    {
        // Arrange & Act
        $uniqueOrderNumber = new UniqueOrderNumber('2025-01-12345');

        // Assert
        $this->assertSame('2025-01-12345', $uniqueOrderNumber->getValue());
    }

    public function testShouldThrowExceptionWhenFormatIsInvalid(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unique order number must be in format YYYY-MM-NNNNN');

        // Act
        new UniqueOrderNumber('invalid-format');
    }

    public function testShouldThrowExceptionWhenValueIsEmpty(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unique order number cannot be empty');

        // Act
        new UniqueOrderNumber('');
    }

    public function testShouldReturnTrueWhenUniqueOrderNumbersAreEqual(): void
    {
        // Arrange
        $uniqueOrderNumber1 = new UniqueOrderNumber('2025-01-12345');
        $uniqueOrderNumber2 = new UniqueOrderNumber('2025-01-12345');

        // Act & Assert
        $this->assertTrue($uniqueOrderNumber1->equals($uniqueOrderNumber2));
    }
}
