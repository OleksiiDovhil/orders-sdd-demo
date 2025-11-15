<?php

declare(strict_types=1);

namespace App\Application\Order\DTO;

final readonly class CreateOrderResponseDTO
{
    public function __construct(
        public string $uniqueOrderNumber
    ) {
    }
}
