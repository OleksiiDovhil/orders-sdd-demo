<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Order\Query;

use App\Application\Order\DTO\GetRecentOrdersResponseDTO;
use App\Application\Order\DTO\OrderItemDTO;
use App\Application\Order\DTO\OrderListItemDTO;
use App\Application\Order\Query\GetRecentOrdersHandler;
use App\Application\Order\Query\GetRecentOrdersQuery;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Entity\OrderItem;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Order\ValueObject\ContractorType;
use App\Domain\Order\ValueObject\OrderId;
use App\Domain\Order\ValueObject\OrderNumber;
use App\Domain\Order\ValueObject\UniqueOrderNumber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetRecentOrdersHandlerTest extends TestCase
{
    private OrderRepositoryInterface&MockObject $repository;
    private GetRecentOrdersHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(OrderRepositoryInterface::class);
        $this->handler = new GetRecentOrdersHandler($this->repository);
    }

    /**
     * @param OrderItem[] $items
     */
    private function createOrder(
        int $id,
        string $uniqueOrderNumber,
        int $sum,
        ContractorType $contractorType,
        array $items = []
    ): Order {
        if (empty($items)) {
            $items = [new OrderItem(1, 1000, 1)];
        }

        return new Order(
            new OrderId($id),
            new OrderNumber(1),
            new UniqueOrderNumber($uniqueOrderNumber),
            $sum,
            $contractorType,
            false,
            ...$items
        );
    }

    public function testShouldReturnEmptyArrayWhenNoOrdersExist(): void
    {
        // Arrange
        $query = new GetRecentOrdersQuery(5);

        $this->repository
            ->expects($this->once())
            ->method('findRecentOrders')
            ->with(5)
            ->willReturn([]);

        // Act
        $result = $this->handler->handle($query);

        // Assert
        $this->assertInstanceOf(GetRecentOrdersResponseDTO::class, $result);
        $this->assertEmpty($result->orders);
    }

    public function testShouldReturnOrdersWithCorrectStructure(): void
    {
        // Arrange
        $query = new GetRecentOrdersQuery(2);
        $order1 = $this->createOrder(
            1,
            '2025-11-1',
            1000,
            ContractorType::INDIVIDUAL,
            [
                new OrderItem(1, 500, 2),
            ]
        );
        $order2 = $this->createOrder(
            2,
            '2025-11-2',
            2000,
            ContractorType::LEGAL_ENTITY,
            [
                new OrderItem(2, 1000, 2),
            ]
        );

        $this->repository
            ->expects($this->once())
            ->method('findRecentOrders')
            ->with(2)
            ->willReturn([$order1, $order2]);

        // Act
        $result = $this->handler->handle($query);

        // Assert
        $this->assertInstanceOf(GetRecentOrdersResponseDTO::class, $result);
        $this->assertCount(2, $result->orders);

        $firstOrder = $result->orders[0];
        $this->assertInstanceOf(OrderListItemDTO::class, $firstOrder);
        $this->assertEquals('2025-11-1', $firstOrder->uniqueOrderNumber);
        $this->assertEquals(1000, $firstOrder->sum);
        $this->assertEquals(ContractorType::INDIVIDUAL->value, $firstOrder->contractorType);
        $this->assertCount(1, $firstOrder->items);

        $firstItem = $firstOrder->items[0];
        $this->assertInstanceOf(OrderItemDTO::class, $firstItem);
        $this->assertEquals(1, $firstItem->productId);
        $this->assertEquals(500, $firstItem->price);
        $this->assertEquals(2, $firstItem->quantity);

        $secondOrder = $result->orders[1];
        $this->assertInstanceOf(OrderListItemDTO::class, $secondOrder);
        $this->assertEquals('2025-11-2', $secondOrder->uniqueOrderNumber);
        $this->assertEquals(2000, $secondOrder->sum);
        $this->assertEquals(ContractorType::LEGAL_ENTITY->value, $secondOrder->contractorType);
    }

    public function testShouldReturnOrdersWithMultipleItems(): void
    {
        // Arrange
        $query = new GetRecentOrdersQuery(1);
        $order = $this->createOrder(
            1,
            '2025-11-1',
            3000,
            ContractorType::INDIVIDUAL,
            [
                new OrderItem(1, 500, 2),
                new OrderItem(2, 1000, 2),
            ]
        );

        $this->repository
            ->expects($this->once())
            ->method('findRecentOrders')
            ->with(1)
            ->willReturn([$order]);

        // Act
        $result = $this->handler->handle($query);

        // Assert
        $this->assertInstanceOf(GetRecentOrdersResponseDTO::class, $result);
        $this->assertCount(1, $result->orders);

        $orderDTO = $result->orders[0];
        $this->assertCount(2, $orderDTO->items);

        $this->assertEquals(1, $orderDTO->items[0]->productId);
        $this->assertEquals(500, $orderDTO->items[0]->price);
        $this->assertEquals(2, $orderDTO->items[0]->quantity);

        $this->assertEquals(2, $orderDTO->items[1]->productId);
        $this->assertEquals(1000, $orderDTO->items[1]->price);
        $this->assertEquals(2, $orderDTO->items[1]->quantity);
    }

    public function testShouldRespectLimitParameter(): void
    {
        // Arrange
        $query = new GetRecentOrdersQuery(3);

        $this->repository
            ->expects($this->once())
            ->method('findRecentOrders')
            ->with(3)
            ->willReturn([]);

        // Act
        $result = $this->handler->handle($query);

        // Assert
        $this->assertInstanceOf(GetRecentOrdersResponseDTO::class, $result);
    }
}
