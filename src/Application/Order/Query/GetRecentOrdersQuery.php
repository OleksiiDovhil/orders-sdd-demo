<?php

declare(strict_types=1);

namespace App\Application\Order\Query;

final readonly class GetRecentOrdersQuery
{
    public function __construct(
        public int $limit
    ) {
    }
}
