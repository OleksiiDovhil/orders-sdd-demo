<?php

declare(strict_types=1);

namespace App\Infrastructure\Payment;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Service\PaymentStatusServiceInterface;

final class MockPaymentStatusService implements PaymentStatusServiceInterface
{
    public function checkPaymentStatus(Order $order): bool
    {
        // Mock payment service: returns random true/false
        // In production, this would call the actual payment microservice
        return (bool) random_int(0, 1);
    }
}
