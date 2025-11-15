<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Entity\OrderItem;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Order\ValueObject\ContractorType;
use App\Domain\Order\ValueObject\OrderId;
use App\Domain\Order\ValueObject\OrderNumber;
use App\Domain\Order\ValueObject\UniqueOrderNumber;

final class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private readonly \PDO $pdo
    ) {
    }

    public function save(Order $order): void
    {
        $this->pdo->beginTransaction();

        try {
            // Check if order already exists (has non-zero ID)
            if ($order->getId()->getValue() > 0) {
                // Update existing order
                $stmt = $this->pdo->prepare('
                    UPDATE orders
                    SET is_paid = :is_paid
                    WHERE id = :id
                ');

                $stmt->execute([
                    ':is_paid' => $order->isPaid() ? 1 : 0,
                    ':id' => $order->getId()->getValue(),
                ]);
            } else {
                // Insert new order and get the generated ID
                $stmt = $this->pdo->prepare('
                    INSERT INTO orders (order_number, unique_order_number, sum, contractor_type, created_at, is_paid)
                    VALUES (:order_number, :unique_order_number, :sum, :contractor_type, :created_at, :is_paid)
                    RETURNING id
                ');

                $stmt->execute([
                    ':order_number' => $order->getOrderNumber()->getValue(),
                    ':unique_order_number' => $order->getUniqueOrderNumber()->getValue(),
                    ':sum' => $order->getSum(),
                    ':contractor_type' => $order->getContractorType()->value,
                    ':created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
                    ':is_paid' => $order->isPaid() ? 1 : 0,
                ]);

                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                if (!is_array($result) || !isset($result['id']) || !is_numeric($result['id'])) {
                    throw new \RuntimeException('Failed to get order ID after insert');
                }
                $orderId = (int) $result['id'];

                // Insert order items
                $itemStmt = $this->pdo->prepare('
                    INSERT INTO order_items (order_id, product_id, price, quantity)
                    VALUES (:order_id, :product_id, :price, :quantity)
                ');

                foreach ($order->getItems() as $item) {
                    $itemStmt->execute([
                        ':order_id' => $orderId,
                        ':product_id' => $item->getProductId(),
                        ':price' => $item->getPrice(),
                        ':quantity' => $item->getQuantity(),
                    ]);
                }
            }

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getNextOrderNumber(): int
    {
        $now = new \DateTimeImmutable();
        $currentYearMonth = $now->format('Y-m');

        // Use SELECT FOR UPDATE to lock the row for thread-safety
        $this->pdo->beginTransaction();

        try {
            // Find the latest sequence for the current month (derived from created_at)
            $stmt = $this->pdo->prepare('
                SELECT id, sequence_number, created_at
                FROM order_number_sequences
                WHERE DATE_TRUNC(\'month\', created_at) = DATE_TRUNC(\'month\', CURRENT_TIMESTAMP)
                ORDER BY sequence_number DESC
                LIMIT 1
                FOR UPDATE
            ');

            $stmt->execute();
            $sequence = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (
                is_array($sequence)
                && isset($sequence['sequence_number'])
                && isset($sequence['id'])
                && is_numeric($sequence['sequence_number'])
            ) {
                // Update existing sequence - increment for current month
                $nextNumber = (int) $sequence['sequence_number'] + 1;
                $updateStmt = $this->pdo->prepare('
                    UPDATE order_number_sequences
                    SET sequence_number = :sequence_number, updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id
                ');
                $updateStmt->execute([
                    ':sequence_number' => $nextNumber,
                    ':id' => is_numeric($sequence['id'])
                        ? (int) $sequence['id']
                        : throw new \RuntimeException('Invalid sequence ID'),
                ]);
            } else {
                // Create new sequence for this month
                // Find the highest sequence number to ensure uniqueness
                $maxStmt = $this->pdo->query(
                    'SELECT COALESCE(MAX(sequence_number), 0) as max_seq FROM order_number_sequences'
                );
                if ($maxStmt === false) {
                    throw new \RuntimeException('Failed to query max sequence number');
                }
                $maxResult = $maxStmt->fetch(\PDO::FETCH_ASSOC);
                if (!is_array($maxResult) || !isset($maxResult['max_seq']) || !is_numeric($maxResult['max_seq'])) {
                    throw new \RuntimeException('Failed to get max sequence number');
                }
                $nextNumber = (int) $maxResult['max_seq'] + 1;

                $insertStmt = $this->pdo->prepare('
                    INSERT INTO order_number_sequences (sequence_number, created_at)
                    VALUES (:sequence_number, CURRENT_TIMESTAMP)
                ');
                $insertStmt->execute([
                    ':sequence_number' => $nextNumber,
                ]);
            }

            $this->pdo->commit();
            return $nextNumber;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function findByUniqueOrderNumber(UniqueOrderNumber $uniqueOrderNumber): ?Order
    {
        // Find order by unique order number
        $stmt = $this->pdo->prepare('
            SELECT id, order_number, unique_order_number, sum, contractor_type, created_at, is_paid
            FROM orders
            WHERE unique_order_number = :unique_order_number
        ');

        $stmt->execute([
            ':unique_order_number' => $uniqueOrderNumber->getValue(),
        ]);

        $orderData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!is_array($orderData) || !isset($orderData['id']) || !is_numeric($orderData['id'])) {
            return null;
        }

        // Find order items
        $itemStmt = $this->pdo->prepare('
            SELECT product_id, price, quantity
            FROM order_items
            WHERE order_id = :order_id
            ORDER BY id
        ');

        $itemStmt->execute([
            ':order_id' => (int) $orderData['id'],
        ]);

        $itemsData = $itemStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Build order items
        $orderItems = array_map(
            /**
             * @param array{product_id: int|string, price: int|string, quantity: int|string} $itemData
             */
            fn (array $itemData) => new OrderItem(
                (int) $itemData['product_id'],
                (int) $itemData['price'],
                (int) $itemData['quantity']
            ),
            $itemsData
        );

        // Reconstruct Order entity
        if (
            !isset($orderData['order_number']) || !is_numeric($orderData['order_number']) ||
            !isset($orderData['sum']) || !is_numeric($orderData['sum']) ||
            !isset($orderData['contractor_type']) || !is_numeric($orderData['contractor_type']) ||
            !isset($orderData['unique_order_number']) || !is_string($orderData['unique_order_number']) ||
            !isset($orderData['created_at']) || !is_string($orderData['created_at'])
        ) {
            throw new \RuntimeException('Invalid order data structure');
        }
        return new Order(
            new OrderId((int) $orderData['id']),
            new OrderNumber((int) $orderData['order_number']),
            new UniqueOrderNumber($orderData['unique_order_number']),
            (int) $orderData['sum'],
            ContractorType::fromInt((int) $orderData['contractor_type']),
            new \DateTimeImmutable($orderData['created_at']),
            (bool) $orderData['is_paid'],
            ...$orderItems
        );
    }

    public function isPaid(OrderId $orderId): bool
    {
        $stmt = $this->pdo->prepare('
            SELECT is_paid
            FROM orders
            WHERE id = :id
        ');

        $stmt->execute([
            ':id' => $orderId->getValue(),
        ]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return is_array($result) && isset($result['is_paid']) && (bool) $result['is_paid'];
    }

    public function markAsPaid(OrderId $orderId): void
    {
        $stmt = $this->pdo->prepare('
            UPDATE orders
            SET is_paid = true
            WHERE id = :id
        ');

        $stmt->execute([
            ':id' => $orderId->getValue(),
        ]);
    }

    /**
     * @return Order[]
     */
    public function findRecentOrders(int $limit): array
    {
        // Use subquery to get most recent order IDs first, then JOIN to load orders and items
        // This ensures we get exactly the N most recent orders, not N rows total
        $stmt = $this->pdo->prepare('
            SELECT 
                o.id,
                o.order_number,
                o.unique_order_number,
                o.sum,
                o.contractor_type,
                o.created_at,
                o.is_paid,
                oi.product_id,
                oi.price,
                oi.quantity
            FROM (
                SELECT id
                FROM orders
                ORDER BY created_at DESC
                LIMIT :limit
            ) recent_orders
            JOIN orders o ON recent_orders.id = o.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            ORDER BY o.created_at DESC
        ');

        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($results)) {
            return [];
        }

        // Group results by order ID
        $ordersData = [];
        foreach ($results as $row) {
            $orderId = (int) $row['id'];
            if (!isset($ordersData[$orderId])) {
                $ordersData[$orderId] = [
                    'id' => $orderId,
                    'order_number' => (int) $row['order_number'],
                    'unique_order_number' => $row['unique_order_number'],
                    'sum' => (int) $row['sum'],
                    'contractor_type' => (int) $row['contractor_type'],
                    'created_at' => $row['created_at'],
                    'is_paid' => (bool) $row['is_paid'],
                    'items' => [],
                ];
            }

            // Add item if product_id is not null (LEFT JOIN may return null for orders without items)
            if ($row['product_id'] !== null) {
                $ordersData[$orderId]['items'][] = [
                    'product_id' => (int) $row['product_id'],
                    'price' => (int) $row['price'],
                    'quantity' => (int) $row['quantity'],
                ];
            }
        }

        // Build Order entities
        $orders = [];
        foreach ($ordersData as $orderData) {
            $orderItems = array_map(
                /**
                 * @param array{product_id: int, price: int, quantity: int} $itemData
                 */
                fn (array $itemData) => new OrderItem(
                    $itemData['product_id'],
                    $itemData['price'],
                    $itemData['quantity']
                ),
                $orderData['items']
            );

            $orders[] = new Order(
                new OrderId($orderData['id']),
                new OrderNumber($orderData['order_number']),
                new UniqueOrderNumber($orderData['unique_order_number']),
                $orderData['sum'],
                ContractorType::fromInt($orderData['contractor_type']),
                new \DateTimeImmutable($orderData['created_at']),
                $orderData['is_paid'],
                ...$orderItems
            );
        }

        return $orders;
    }
}
