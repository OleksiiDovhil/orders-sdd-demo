<?php

declare(strict_types=1);

namespace App\Application\Order\Query;

use App\Application\Order\DTO\CheckOrderCompletionResponseDTO;
use App\Application\Order\ValueObject\OrderCompletionMessage;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Order\Service\PaymentStatusServiceInterface;
use App\Domain\Order\ValueObject\UniqueOrderNumber;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CheckOrderCompletionHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly PaymentStatusServiceInterface $paymentStatusService
    ) {
    }

    public function handle(CheckOrderCompletionQuery $query): CheckOrderCompletionResponseDTO
    {
        $uniqueOrderNumber = new UniqueOrderNumber($query->uniqueOrderNumber);
        $order = $this->orderRepository->findByUniqueOrderNumber($uniqueOrderNumber);

        if ($order === null) {
            throw new NotFoundHttpException('Order not found');
        }

        // First, check if order is already marked as paid
        $isPaid = $order->isPaid();

        // If not paid, check with payment service for both contractor types
        if (!$isPaid) {
            $servicePaidStatus = $this->paymentStatusService->checkPaymentStatus($order);

            // If service confirms payment, mark the order as paid and save
            if ($servicePaidStatus) {
                $order->markAsPaid();
                $this->orderRepository->save($order);
                $isPaid = true;
            }
        }

        $message = $isPaid
            ? OrderCompletionMessage::PAID
            : OrderCompletionMessage::PENDING;

        return new CheckOrderCompletionResponseDTO($isPaid, $message);
    }
}
