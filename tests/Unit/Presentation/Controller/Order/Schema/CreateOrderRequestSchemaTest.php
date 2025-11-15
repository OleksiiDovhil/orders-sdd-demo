<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Controller\Order\Schema;

use App\Presentation\Controller\Order\Schema\CreateOrderRequestSchema;
use PHPUnit\Framework\TestCase;

final class CreateOrderRequestSchemaTest extends TestCase
{
    public function testShouldCreateSchemaInstance(): void
    {
        // Arrange & Act
        $schema = new CreateOrderRequestSchema();

        // Assert
        $this->assertInstanceOf(CreateOrderRequestSchema::class, $schema);
    }

    public function testShouldHaveProperties(): void
    {
        // Arrange
        $schema = new CreateOrderRequestSchema();

        // Act & Assert
        $schema->sum = 1000;
        $schema->contractorType = 1;
        $schema->items = [];

        $this->assertEquals(1000, $schema->sum);
        $this->assertEquals(1, $schema->contractorType);
        $this->assertIsArray($schema->items);
    }
}
