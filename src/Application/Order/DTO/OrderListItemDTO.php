<?php

declare(strict_types=1);

namespace App\Application\Order\DTO;

final readonly class OrderListItemDTO
{
    /**
     * @param OrderItemDTO[] $items
     */
    public function __construct(
        public string $id,
        public int $sum,
        public int $contractorType,
        public array $items
    ) {
    }
}
