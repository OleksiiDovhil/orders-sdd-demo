<?php

declare(strict_types=1);

namespace App\Presentation\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateOrderItemRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Product ID is required')]
        #[Assert\Type(type: 'integer', message: 'Product ID must be an integer')]
        #[Assert\Positive(message: 'Product ID must be a positive integer')]
        public int $productId,
        #[Assert\NotBlank(message: 'Price is required')]
        #[Assert\Type(type: 'integer', message: 'Price must be an integer')]
        #[Assert\GreaterThanOrEqual(value: 0, message: 'Price must be non-negative')]
        public int $price,
        #[Assert\NotBlank(message: 'Quantity is required')]
        #[Assert\Type(type: 'integer', message: 'Quantity must be an integer')]
        #[Assert\Positive(message: 'Quantity must be a positive integer')]
        public int $quantity
    ) {
    }
}
