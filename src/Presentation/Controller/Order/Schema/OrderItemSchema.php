<?php

declare(strict_types=1);

namespace App\Presentation\Controller\Order\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OrderItem',
    type: 'object',
    required: ['productId', 'price', 'quantity'],
    description: 'An item within an order'
)]
final class OrderItemSchema
{
    #[OA\Property(
        property: 'productId',
        type: 'integer',
        description: 'Product identifier',
        example: 1
    )]
    public int $productId;

    #[OA\Property(
        property: 'price',
        type: 'integer',
        description: 'Product price in cents',
        example: 1000
    )]
    public int $price;

    #[OA\Property(
        property: 'quantity',
        type: 'integer',
        description: 'Product quantity',
        minimum: 1,
        example: 1
    )]
    public int $quantity;
}
