<?php

declare(strict_types=1);

namespace App\Domain\Order\ValueObject;

final readonly class UniqueOrderNumber
{
    public function __construct(
        private string $value
    ) {
        if (empty($this->value)) {
            throw new \InvalidArgumentException('Unique order number cannot be empty');
        }

        // Validate format: YYYY-MM-NNNNN
        if (!preg_match('/^\d{4}-\d{2}-\d+$/', $this->value)) {
            throw new \InvalidArgumentException(
                'Unique order number must be in format YYYY-MM-NNNNN (e.g., 2020-09-12345)'
            );
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(UniqueOrderNumber $other): bool
    {
        return $this->value === $other->value;
    }
}
