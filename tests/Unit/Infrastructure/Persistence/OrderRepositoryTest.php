<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Persistence;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Entity\OrderItem;
use App\Domain\Order\ValueObject\ContractorType;
use App\Domain\Order\ValueObject\OrderId;
use App\Domain\Order\ValueObject\OrderNumber;
use App\Domain\Order\ValueObject\UniqueOrderNumber;
use App\Infrastructure\Persistence\OrderRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class OrderRepositoryTest extends TestCase
{
    private \PDO&MockObject $pdo;
    private OrderRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(\PDO::class);
        $this->repository = new OrderRepository($this->pdo);
    }

    /**
     * @param array<OrderItem> $items
     */
    private function createOrder(
        int $orderId = 0,
        bool $isPaid = false,
        array $items = []
    ): Order {
        if (empty($items)) {
            $items = [new OrderItem(1, 1000, 1)];
        }

        return new Order(
            new OrderId($orderId),
            new OrderNumber(1),
            new UniqueOrderNumber('2025-11-1'),
            1000,
            ContractorType::INDIVIDUAL,
            $isPaid,
            ...$items
        );
    }

    public function testShouldSaveNewOrderWithInsert(): void
    {
        // Arrange
        $order = $this->createOrder(0, false);
        $stmt = $this->createMock(\PDOStatement::class);
        $itemStmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('beginTransaction');

        $this->pdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnCallback(function (string $sql) use ($stmt, $itemStmt) {
                if (str_contains($sql, 'INSERT INTO orders')) {
                    return $stmt;
                }
                if (str_contains($sql, 'INSERT INTO order_items')) {
                    return $itemStmt;
                }
                return $stmt;
            });

        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                return isset($params[':order_number']) &&
                    isset($params[':unique_order_number']) &&
                    isset($params[':sum']) &&
                    isset($params[':contractor_type']) &&
                    isset($params[':is_paid']) &&
                    !isset($params[':created_at']); // created_at is now set via CURRENT_TIMESTAMP in SQL
            }));

        $stmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(['id' => '1']);

        $itemStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                return isset($params[':order_id']) &&
                    isset($params[':product_id']) &&
                    isset($params[':price']) &&
                    isset($params[':quantity']);
            }));

        $this->pdo->expects($this->once())
            ->method('commit');

        // Act
        $this->repository->save($order);
    }

    public function testShouldSaveExistingOrderWithUpdate(): void
    {
        // Arrange
        $order = $this->createOrder(1, true);
        $stmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('beginTransaction');

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('UPDATE orders'))
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                return isset($params[':is_paid']) &&
                    $params[':is_paid'] === 1 &&
                    isset($params[':id']) &&
                    $params[':id'] === 1;
            }));

        $this->pdo->expects($this->once())
            ->method('commit');

        // Act
        $this->repository->save($order);
    }

    public function testShouldRollbackTransactionOnException(): void
    {
        // Arrange
        $order = $this->createOrder(0, false);
        $stmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('beginTransaction');

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->willThrowException(new \RuntimeException('Database error'));

        $this->pdo->expects($this->once())
            ->method('rollBack');

        $this->pdo->expects($this->never())
            ->method('commit');

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Database error');

        // Act
        $this->repository->save($order);
    }

    public function testShouldSaveOrderWithMultipleItems(): void
    {
        // Arrange
        $items = [
            new OrderItem(1, 1000, 2),
            new OrderItem(2, 2000, 1),
        ];
        $order = $this->createOrder(0, false, $items);
        $stmt = $this->createMock(\PDOStatement::class);
        $itemStmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('beginTransaction');

        $this->pdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($stmt, $itemStmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $stmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(['id' => '1']);

        $itemStmt->expects($this->exactly(2))
            ->method('execute');

        $this->pdo->expects($this->once())
            ->method('commit');

        // Act
        $this->repository->save($order);
    }

    public function testShouldGetNextOrderNumberWithExistingSequence(): void
    {
        // Arrange
        $selectStmt = $this->createMock(\PDOStatement::class);
        $updateStmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('beginTransaction');

        $this->pdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($selectStmt, $updateStmt);

        $selectStmt->expects($this->once())
            ->method('execute');

        $selectStmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn([
                'id' => '1',
                'sequence_number' => '5',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

        $updateStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                return isset($params[':sequence_number']) &&
                    $params[':sequence_number'] === 6 &&
                    isset($params[':id']) &&
                    $params[':id'] === 1;
            }));

        $this->pdo->expects($this->once())
            ->method('commit');

        // Act
        $result = $this->repository->getNextOrderNumber();

        // Assert
        $this->assertEquals(6, $result);
    }

    public function testShouldGetNextOrderNumberWithNewSequenceCreation(): void
    {
        // Arrange
        $selectStmt = $this->createMock(\PDOStatement::class);
        $maxStmt = $this->createMock(\PDOStatement::class);
        $insertStmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('beginTransaction');

        $this->pdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($selectStmt, $insertStmt);

        $selectStmt->expects($this->once())
            ->method('execute');

        $selectStmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(false);

        $this->pdo->expects($this->once())
            ->method('query')
            ->with($this->stringContains('SELECT COALESCE(MAX(sequence_number)'))
            ->willReturn($maxStmt);

        $maxStmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(['max_seq' => '10']);

        $insertStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                return isset($params[':sequence_number']) &&
                    $params[':sequence_number'] === 11;
            }));

        $this->pdo->expects($this->once())
            ->method('commit');

        // Act
        $result = $this->repository->getNextOrderNumber();

        // Assert
        $this->assertEquals(11, $result);
    }

    public function testShouldRollbackTransactionOnExceptionInGetNextOrderNumber(): void
    {
        // Arrange
        $stmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('beginTransaction');

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->willThrowException(new \RuntimeException('Database error'));

        $this->pdo->expects($this->once())
            ->method('rollBack');

        $this->pdo->expects($this->never())
            ->method('commit');

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Database error');

        // Act
        $this->repository->getNextOrderNumber();
    }

    public function testShouldFindByUniqueOrderNumberWithExistingOrder(): void
    {
        // Arrange
        $uniqueOrderNumber = new UniqueOrderNumber('2025-11-12345');
        $orderStmt = $this->createMock(\PDOStatement::class);
        $itemStmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($orderStmt, $itemStmt);

        $orderStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                return isset($params[':unique_order_number']) &&
                    $params[':unique_order_number'] === '2025-11-12345';
            }));

        $orderStmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn([
                'id' => '1',
                'order_number' => '12345',
                'unique_order_number' => '2025-11-12345',
                'sum' => '1000',
                'contractor_type' => '1',
                'created_at' => '2025-11-15 10:00:00',
                'is_paid' => '0',
            ]);

        $itemStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                return isset($params[':order_id']) && $params[':order_id'] === 1;
            }));

        $itemStmt->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn([
                [
                    'product_id' => '1',
                    'price' => '1000',
                    'quantity' => '1',
                ],
            ]);

        // Act
        $result = $this->repository->findByUniqueOrderNumber($uniqueOrderNumber);

        // Assert
        $this->assertInstanceOf(Order::class, $result);
        $this->assertEquals(1, $result->getId()->getValue());
        $this->assertEquals('2025-11-12345', $result->getUniqueOrderNumber()->getValue());
    }

    public function testShouldReturnNullWhenOrderNotFound(): void
    {
        // Arrange
        $uniqueOrderNumber = new UniqueOrderNumber('2025-11-99999');
        $stmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute');

        $stmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(false);

        // Act
        $result = $this->repository->findByUniqueOrderNumber($uniqueOrderNumber);

        // Assert
        $this->assertNull($result);
    }

    public function testShouldThrowExceptionWhenOrderDataStructureIsInvalid(): void
    {
        // Arrange
        $uniqueOrderNumber = new UniqueOrderNumber('2025-11-12345');
        $orderStmt = $this->createMock(\PDOStatement::class);
        $itemStmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($orderStmt, $itemStmt);

        $orderStmt->expects($this->once())
            ->method('execute');

        $orderStmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn([
                'id' => '1',
                // Missing required fields
            ]);

        $itemStmt->expects($this->once())
            ->method('execute');

        $itemStmt->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn([]);

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid order data structure');

        // Act
        $this->repository->findByUniqueOrderNumber($uniqueOrderNumber);
    }

    public function testShouldReturnTrueWhenOrderIsPaid(): void
    {
        // Arrange
        $orderId = new OrderId(1);
        $stmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                return isset($params[':id']) && $params[':id'] === 1;
            }));

        $stmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(['is_paid' => '1']);

        // Act
        $result = $this->repository->isPaid($orderId);

        // Assert
        $this->assertTrue($result);
    }

    public function testShouldReturnFalseWhenOrderIsUnpaid(): void
    {
        // Arrange
        $orderId = new OrderId(1);
        $stmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute');

        $stmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(['is_paid' => '0']);

        // Act
        $result = $this->repository->isPaid($orderId);

        // Assert
        $this->assertFalse($result);
    }

    public function testShouldReturnFalseWhenOrderDoesNotExist(): void
    {
        // Arrange
        $orderId = new OrderId(999);
        $stmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute');

        $stmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(false);

        // Act
        $result = $this->repository->isPaid($orderId);

        // Assert
        $this->assertFalse($result);
    }

    public function testShouldMarkOrderAsPaid(): void
    {
        // Arrange
        $orderId = new OrderId(1);
        $stmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('UPDATE orders'))
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params): bool {
                return isset($params[':id']) && $params[':id'] === 1;
            }));

        // Act
        $this->repository->markAsPaid($orderId);
    }

    public function testShouldThrowExceptionWhenFetchReturnsInvalidResultAfterInsert(): void
    {
        // Arrange
        $order = $this->createOrder(0, false);
        $stmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('beginTransaction');

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('execute');

        $stmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(false); // Invalid result - no id

        $this->pdo->expects($this->once())
            ->method('rollBack');

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to get order ID after insert');

        // Act
        $this->repository->save($order);
    }

    public function testShouldThrowExceptionWhenQueryReturnsFalse(): void
    {
        // Arrange
        $selectStmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('beginTransaction');

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($selectStmt);

        $selectStmt->expects($this->once())
            ->method('execute');

        $selectStmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(false);

        $this->pdo->expects($this->once())
            ->method('query')
            ->willReturn(false); // Query returns false

        $this->pdo->expects($this->once())
            ->method('rollBack');

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to query max sequence number');

        // Act
        $this->repository->getNextOrderNumber();
    }

    public function testShouldThrowExceptionWhenMaxResultIsInvalid(): void
    {
        // Arrange
        $selectStmt = $this->createMock(\PDOStatement::class);
        $maxStmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('beginTransaction');

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($selectStmt);

        $selectStmt->expects($this->once())
            ->method('execute');

        $selectStmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(false);

        $this->pdo->expects($this->once())
            ->method('query')
            ->willReturn($maxStmt);

        $maxStmt->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(false); // Invalid result

        $this->pdo->expects($this->once())
            ->method('rollBack');

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to get max sequence number');

        // Act
        $this->repository->getNextOrderNumber();
    }

    public function testShouldReturnEmptyArrayWhenNoRecentOrdersExist(): void
    {
        // Arrange
        $stmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('bindValue')
            ->with(':limit', 5, \PDO::PARAM_INT);

        $stmt->expects($this->once())
            ->method('execute');

        $stmt->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn([]);

        // Act
        $result = $this->repository->findRecentOrders(5);

        // Assert
        $this->assertEmpty($result);
    }

    public function testShouldReturnRecentOrdersWithItems(): void
    {
        // Arrange
        $stmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('bindValue')
            ->with(':limit', 2, \PDO::PARAM_INT);

        $stmt->expects($this->once())
            ->method('execute');

        $stmt->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn([
                [
                    'id' => '1',
                    'order_number' => '1',
                    'unique_order_number' => '2025-11-1',
                    'sum' => '1000',
                    'contractor_type' => '1',
                    'created_at' => '2025-11-15 10:00:00',
                    'is_paid' => '0',
                    'product_id' => '1',
                    'price' => '500',
                    'quantity' => '2',
                ],
                [
                    'id' => '1',
                    'order_number' => '1',
                    'unique_order_number' => '2025-11-1',
                    'sum' => '1000',
                    'contractor_type' => '1',
                    'created_at' => '2025-11-15 10:00:00',
                    'is_paid' => '0',
                    'product_id' => '2',
                    'price' => '500',
                    'quantity' => '1',
                ],
                [
                    'id' => '2',
                    'order_number' => '2',
                    'unique_order_number' => '2025-11-2',
                    'sum' => '2000',
                    'contractor_type' => '2',
                    'created_at' => '2025-11-15 09:00:00',
                    'is_paid' => '0',
                    'product_id' => '3',
                    'price' => '2000',
                    'quantity' => '1',
                ],
            ]);

        // Act
        $result = $this->repository->findRecentOrders(2);

        // Assert
        $this->assertCount(2, $result);

        $firstOrder = $result[0];
        $this->assertInstanceOf(Order::class, $firstOrder);
        $this->assertEquals(1, $firstOrder->getId()->getValue());
        $this->assertEquals('2025-11-1', $firstOrder->getUniqueOrderNumber()->getValue());
        $this->assertEquals(1000, $firstOrder->getSum());
        $this->assertEquals(ContractorType::INDIVIDUAL, $firstOrder->getContractorType());
        $this->assertCount(2, $firstOrder->getItems());

        $secondOrder = $result[1];
        $this->assertInstanceOf(Order::class, $secondOrder);
        $this->assertEquals(2, $secondOrder->getId()->getValue());
        $this->assertEquals('2025-11-2', $secondOrder->getUniqueOrderNumber()->getValue());
        $this->assertEquals(2000, $secondOrder->getSum());
        $this->assertEquals(ContractorType::LEGAL_ENTITY, $secondOrder->getContractorType());
        $this->assertCount(1, $secondOrder->getItems());
    }

    public function testShouldReturnOrderWithoutItemsWhenNoItemsExist(): void
    {
        // Arrange
        $stmt = $this->createMock(\PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $stmt->expects($this->once())
            ->method('bindValue')
            ->with(':limit', 1, \PDO::PARAM_INT);

        $stmt->expects($this->once())
            ->method('execute');

        $stmt->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn([
                [
                    'id' => '1',
                    'order_number' => '1',
                    'unique_order_number' => '2025-11-1',
                    'sum' => '1000',
                    'contractor_type' => '1',
                    'created_at' => '2025-11-15 10:00:00',
                    'is_paid' => '0',
                    'product_id' => null,
                    'price' => null,
                    'quantity' => null,
                ],
            ]);

        // Act
        $result = $this->repository->findRecentOrders(1);

        // Assert
        $this->assertCount(1, $result);

        $order = $result[0];
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEmpty($order->getItems());
    }
}
