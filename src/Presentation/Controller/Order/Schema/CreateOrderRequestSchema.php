<?php

declare(strict_types=1);

namespace App\Presentation\Controller\Order\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateOrderRequest',
    type: 'object',
    required: ['sum', 'contractorType', 'items'],
    description: 'Request body for creating a new order'
)]
final class CreateOrderRequestSchema
{
    #[OA\Property(
        property: 'sum',
        type: 'integer',
        description: 'Total order amount in cents',
        example: 1000
    )]
    public int $sum;

    #[OA\Property(
        property: 'contractorType',
        type: 'integer',
        description: 'Contractor type: 1 = individual, 2 = legal entity',
        enum: [1, 2],
        example: 1
    )]
    public int $contractorType;

    #[OA\Property(
        property: 'items',
        type: 'array',
        description: 'Array of order items',
        items: new OA\Items(
            ref: '#/components/schemas/OrderItem'
        )
    )]
    public array $items;
}

