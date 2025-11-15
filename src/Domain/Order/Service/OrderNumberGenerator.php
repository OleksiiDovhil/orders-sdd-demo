<?php

declare(strict_types=1);

namespace App\Domain\Order\Service;

use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Order\ValueObject\OrderNumber;
use App\Domain\Order\ValueObject\UniqueOrderNumber;

final class OrderNumberGenerator
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * @return array{orderNumber: OrderNumber, uniqueOrderNumber: UniqueOrderNumber}
     */
    public function generate(): array
    {
        $orderNumber = $this->orderRepository->getNextOrderNumber();
        $uniqueOrderNumber = $this->formatUniqueOrderNumber($orderNumber);

        return [
            'orderNumber' => new OrderNumber($orderNumber),
            'uniqueOrderNumber' => new UniqueOrderNumber($uniqueOrderNumber),
        ];
    }

    private function formatUniqueOrderNumber(int $orderNumber): string
    {
        $now = new \DateTimeImmutable();
        $year = $now->format('Y');
        $month = $now->format('m');

        return sprintf('%s-%s-%d', $year, $month, $orderNumber);
    }
}

