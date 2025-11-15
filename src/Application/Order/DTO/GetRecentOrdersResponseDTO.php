<?php

declare(strict_types=1);

namespace App\Application\Order\DTO;

final readonly class GetRecentOrdersResponseDTO
{
    /**
     * @param OrderListItemDTO[] $orders
     */
    public function __construct(
        public array $orders
    ) {
    }
}
