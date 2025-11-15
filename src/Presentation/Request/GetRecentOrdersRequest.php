<?php

declare(strict_types=1);

namespace App\Presentation\Request;

use App\Application\Order\Query\GetRecentOrdersQuery;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    schema: 'GetRecentOrdersRequest',
    type: 'object',
    required: ['limit'],
    description: 'Request parameters for getting recent orders'
)]
final readonly class GetRecentOrdersRequest
{
    public function __construct(
        #[OA\Property(
            property: 'limit',
            type: 'integer',
            description: 'Maximum number of orders to return',
            minimum: 1,
            maximum: 1000,
            example: 5
        )]
        #[Assert\NotBlank(message: 'Limit is required')]
        #[Assert\Type(type: 'integer', message: 'Limit must be an integer')]
        #[Assert\GreaterThan(value: 0, message: 'Limit must be greater than 0')]
        #[Assert\LessThanOrEqual(value: 1000, message: 'Limit must not exceed 1000')]
        public int $limit
    ) {
    }

    public function createQuery(): GetRecentOrdersQuery
    {
        return new GetRecentOrdersQuery($this->limit);
    }
}
