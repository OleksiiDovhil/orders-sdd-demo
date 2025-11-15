<?php

declare(strict_types=1);

namespace App\Application\Order\DTO;

final readonly class OrderItemDTO
{
    public function __construct(
        public int $productId,
        public int $price,
        public int $quantity
    ) {
    }
}
