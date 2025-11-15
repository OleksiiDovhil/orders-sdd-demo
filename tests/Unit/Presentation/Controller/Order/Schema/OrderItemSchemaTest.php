<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Controller\Order\Schema;

use App\Presentation\Controller\Order\Schema\OrderItemSchema;
use PHPUnit\Framework\TestCase;

final class OrderItemSchemaTest extends TestCase
{
    public function testShouldCreateSchemaInstance(): void
    {
        // Arrange & Act
        $schema = new OrderItemSchema();

        // Assert
        $this->assertInstanceOf(OrderItemSchema::class, $schema);
    }

    public function testShouldHaveProperties(): void
    {
        // Arrange
        $schema = new OrderItemSchema();

        // Act & Assert
        $schema->productId = 1;
        $schema->price = 1000;
        $schema->quantity = 2;

        $this->assertEquals(1, $schema->productId);
        $this->assertEquals(1000, $schema->price);
        $this->assertEquals(2, $schema->quantity);
    }
}
