<?php

declare(strict_types=1);

namespace App\Domain\Order\ValueObject;

final readonly class OrderNumber
{
    public function __construct(
        private int $value
    ) {
        if ($this->value <= 0) {
            throw new \InvalidArgumentException('Order number must be a positive integer');
        }
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
