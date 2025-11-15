<?php

declare(strict_types=1);

namespace App\Application\Order\DTO;

use App\Application\Order\ValueObject\OrderCompletionMessage;

final readonly class CheckOrderCompletionResponseDTO
{
    public function __construct(
        public bool $isPaid,
        public OrderCompletionMessage $message
    ) {
    }
}
