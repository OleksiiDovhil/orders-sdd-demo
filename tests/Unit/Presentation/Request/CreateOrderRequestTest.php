<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Request;

use App\Application\Order\Command\CreateOrderCommand;
use App\Application\Order\DTO\CreateOrderItemDTO;
use App\Presentation\Request\CreateOrderItemRequest;
use App\Presentation\Request\CreateOrderRequest;
use PHPUnit\Framework\TestCase;

final class CreateOrderRequestTest extends TestCase
{
    public function testShouldCreateCommandFromRequest(): void
    {
        // Arrange
        $items = [
            new CreateOrderItemRequest(1, 1000, 2),
            new CreateOrderItemRequest(2, 2000, 1),
        ];
        $request = new CreateOrderRequest(
            sum: 3000,
            contractorType: 1,
            items: $items
        );

        // Act
        $command = $request->createCommand();

        // Assert
        $this->assertInstanceOf(CreateOrderCommand::class, $command);
        $this->assertEquals(3000, $command->sum);
        $this->assertEquals(1, $command->contractorType);
        $this->assertCount(2, $command->items);
        
        $this->assertInstanceOf(CreateOrderItemDTO::class, $command->items[0]);
        $this->assertEquals(1, $command->items[0]->productId);
        $this->assertEquals(1000, $command->items[0]->price);
        $this->assertEquals(2, $command->items[0]->quantity);
        
        $this->assertInstanceOf(CreateOrderItemDTO::class, $command->items[1]);
        $this->assertEquals(2, $command->items[1]->productId);
        $this->assertEquals(2000, $command->items[1]->price);
        $this->assertEquals(1, $command->items[1]->quantity);
    }

    public function testShouldCreateCommandWithSingleItem(): void
    {
        // Arrange
        $items = [
            new CreateOrderItemRequest(1, 1000, 1),
        ];
        $request = new CreateOrderRequest(
            sum: 1000,
            contractorType: 2,
            items: $items
        );

        // Act
        $command = $request->createCommand();

        // Assert
        $this->assertInstanceOf(CreateOrderCommand::class, $command);
        $this->assertEquals(1000, $command->sum);
        $this->assertEquals(2, $command->contractorType);
        $this->assertCount(1, $command->items);
        $this->assertEquals(1, $command->items[0]->productId);
    }

    public function testShouldCreateCommandWithEmptyItemsArray(): void
    {
        // Arrange
        $request = new CreateOrderRequest(
            sum: 0,
            contractorType: 1,
            items: []
        );

        // Act
        $command = $request->createCommand();

        // Assert
        $this->assertInstanceOf(CreateOrderCommand::class, $command);
        $this->assertEquals(0, $command->sum);
        $this->assertEquals(1, $command->contractorType);
        $this->assertCount(0, $command->items);
    }
}

