<?php

declare(strict_types=1);

namespace App\Domain\Order\Repository;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\ValueObject\OrderId;
use App\Domain\Order\ValueObject\UniqueOrderNumber;

interface OrderRepositoryInterface
{
    public function save(Order $order): void;

    public function getNextOrderNumber(): int;

    public function findByUniqueOrderNumber(UniqueOrderNumber $uniqueOrderNumber): ?Order;

    public function isPaid(OrderId $orderId): bool;

    public function markAsPaid(OrderId $orderId): void;
}

