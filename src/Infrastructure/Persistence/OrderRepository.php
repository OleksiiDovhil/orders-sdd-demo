<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Entity\OrderItem;
use App\Domain\Order\Repository\OrderRepositoryInterface;

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
            // Insert order and get the generated ID
            $stmt = $this->pdo->prepare('
                INSERT INTO orders (order_number, unique_order_number, sum, contractor_type, created_at)
                VALUES (:order_number, :unique_order_number, :sum, :contractor_type, :created_at)
                RETURNING id
            ');

            $stmt->execute([
                ':order_number' => $order->getOrderNumber()->getValue(),
                ':unique_order_number' => $order->getUniqueOrderNumber()->getValue(),
                ':sum' => $order->getSum(),
                ':contractor_type' => $order->getContractorType()->value,
                ':created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
            ]);

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
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

            if ($sequence) {
                // Update existing sequence - increment for current month
                $nextNumber = $sequence['sequence_number'] + 1;
                $updateStmt = $this->pdo->prepare('
                    UPDATE order_number_sequences
                    SET sequence_number = :sequence_number, updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id
                ');
                $updateStmt->execute([
                    ':sequence_number' => $nextNumber,
                    ':id' => $sequence['id'],
                ]);
            } else {
                // Create new sequence for this month
                // Find the highest sequence number to ensure uniqueness
                $maxStmt = $this->pdo->query('SELECT COALESCE(MAX(sequence_number), 0) as max_seq FROM order_number_sequences');
                $maxResult = $maxStmt->fetch(\PDO::FETCH_ASSOC);
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

}

