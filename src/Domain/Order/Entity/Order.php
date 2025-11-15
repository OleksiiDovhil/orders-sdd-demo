<?php

declare(strict_types=1);

namespace App\Domain\Order\Entity;

use App\Domain\Order\ValueObject\ContractorType;
use App\Domain\Order\ValueObject\OrderId;
use App\Domain\Order\ValueObject\OrderNumber;
use App\Domain\Order\ValueObject\UniqueOrderNumber;

final class Order
{
    /**
     * @var OrderItem[]
     */
    private array $items = [];

    public function __construct(
        private readonly OrderId $id,
        private readonly OrderNumber $orderNumber,
        private readonly UniqueOrderNumber $uniqueOrderNumber,
        private readonly int $sum,
        private readonly ContractorType $contractorType,
        private readonly \DateTimeImmutable $createdAt,
        private bool $isPaid = false,
        OrderItem ...$items
    ) {
        if ($this->sum < 0) {
            throw new \InvalidArgumentException('Order sum cannot be negative');
        }

        foreach ($items as $item) {
            $this->items[] = $item;
        }
    }

    public function getId(): OrderId
    {
        return $this->id;
    }

    public function getOrderNumber(): OrderNumber
    {
        return $this->orderNumber;
    }

    public function getUniqueOrderNumber(): UniqueOrderNumber
    {
        return $this->uniqueOrderNumber;
    }

    public function getSum(): int
    {
        return $this->sum;
    }

    public function getContractorType(): ContractorType
    {
        return $this->contractorType;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return OrderItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function isPaid(): bool
    {
        return $this->isPaid;
    }

    public function markAsPaid(): void
    {
        $this->isPaid = true;
    }
}
