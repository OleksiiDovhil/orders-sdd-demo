<?php

declare(strict_types=1);

namespace App\Domain\Order\ValueObject;

final readonly class OrderId
{
    public function __construct(
        private int $value
    ) {
        if ($this->value < 0) {
            throw new \InvalidArgumentException('Order ID must be a non-negative integer');
        }
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(OrderId $other): bool
    {
        return $this->value === $other->value;
    }
}
