<?php

declare(strict_types=1);

namespace App\Application\Order\Command;

use App\Application\Order\DTO\CreateOrderItemDTO;

final readonly class CreateOrderCommand
{
    /**
     * @param CreateOrderItemDTO[] $items
     */
    public function __construct(
        public int $sum,
        public int $contractorType,
        public array $items
    ) {
    }
}
