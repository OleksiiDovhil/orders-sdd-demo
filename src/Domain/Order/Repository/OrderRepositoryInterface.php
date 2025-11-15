<?php

declare(strict_types=1);

namespace App\Domain\Order\Repository;

use App\Domain\Order\Entity\Order;

interface OrderRepositoryInterface
{
    public function save(Order $order): void;

    public function getNextOrderNumber(): int;
}

