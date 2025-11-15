<?php

declare(strict_types=1);

namespace App\Application\Order\Query;

final readonly class CheckOrderCompletionQuery
{
    public function __construct(
        public string $uniqueOrderNumber
    ) {
    }
}
