<?php

declare(strict_types=1);

namespace App\Presentation\Request;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    schema: 'OrderItem',
    type: 'object',
    required: ['productId', 'price', 'quantity'],
    description: 'An item within an order'
)]
final readonly class CreateOrderItemRequest
{
    public function __construct(
        #[OA\Property(
            property: 'productId',
            type: 'integer',
            description: 'Product identifier',
            example: 1
        )]
        #[Assert\NotBlank(message: 'Product ID is required')]
        #[Assert\Type(type: 'integer', message: 'Product ID must be an integer')]
        #[Assert\Positive(message: 'Product ID must be a positive integer')]
        public int $productId,
        #[OA\Property(
            property: 'price',
            type: 'integer',
            description: 'Product price in cents',
            example: 1000
        )]
        #[Assert\NotBlank(message: 'Price is required')]
        #[Assert\Type(type: 'integer', message: 'Price must be an integer')]
        #[Assert\GreaterThanOrEqual(value: 0, message: 'Price must be non-negative')]
        public int $price,
        #[OA\Property(
            property: 'quantity',
            type: 'integer',
            description: 'Product quantity',
            minimum: 1,
            example: 1
        )]
        #[Assert\NotBlank(message: 'Quantity is required')]
        #[Assert\Type(type: 'integer', message: 'Quantity must be an integer')]
        #[Assert\Positive(message: 'Quantity must be a positive integer')]
        public int $quantity
    ) {
    }
}
