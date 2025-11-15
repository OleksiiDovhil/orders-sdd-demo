<?php

declare(strict_types=1);

namespace App\Domain\Order\Entity;

final readonly class OrderItem
{
    public function __construct(
        private int $productId,
        private int $price,
        private int $quantity
    ) {
        if ($this->productId <= 0) {
            throw new \InvalidArgumentException('Product ID must be a positive integer');
        }

        if ($this->price < 0) {
            throw new \InvalidArgumentException('Price cannot be negative');
        }

        if ($this->quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be a positive integer');
        }
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}

