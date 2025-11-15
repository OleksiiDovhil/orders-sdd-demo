<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Request;

use App\Presentation\Request\CreateOrderItemRequest;
use PHPUnit\Framework\TestCase;

final class CreateOrderItemRequestTest extends TestCase
{
    public function testShouldCreateItemRequestWithValidData(): void
    {
        // Arrange & Act
        $item = new CreateOrderItemRequest(
            productId: 1,
            price: 1000,
            quantity: 2
        );

        // Assert
        $this->assertEquals(1, $item->productId);
        $this->assertEquals(1000, $item->price);
        $this->assertEquals(2, $item->quantity);
    }

    public function testShouldCreateItemRequestWithZeroPrice(): void
    {
        // Arrange & Act
        $item = new CreateOrderItemRequest(
            productId: 1,
            price: 0,
            quantity: 1
        );

        // Assert
        $this->assertEquals(0, $item->price);
    }
}

