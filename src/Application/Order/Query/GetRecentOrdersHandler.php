<?php

declare(strict_types=1);

namespace App\Application\Order\Query;

use App\Application\Order\DTO\GetRecentOrdersResponseDTO;
use App\Application\Order\DTO\OrderItemDTO;
use App\Application\Order\DTO\OrderListItemDTO;
use App\Domain\Order\Repository\OrderRepositoryInterface;

final class GetRecentOrdersHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {
    }

    public function handle(GetRecentOrdersQuery $query): GetRecentOrdersResponseDTO
    {
        $orders = $this->orderRepository->findRecentOrders($query->limit);

        $orderDTOs = array_map(
            function ($order) {
                $itemDTOs = array_map(
                    fn ($item) => new OrderItemDTO(
                        $item->getProductId(),
                        $item->getPrice(),
                        $item->getQuantity()
                    ),
                    $order->getItems()
                );

                return new OrderListItemDTO(
                    $order->getUniqueOrderNumber()->getValue(),
                    $order->getSum(),
                    $order->getContractorType()->value,
                    $itemDTOs
                );
            },
            $orders
        );

        return new GetRecentOrdersResponseDTO($orderDTOs);
    }
}
