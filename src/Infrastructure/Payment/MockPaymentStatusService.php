<?php

declare(strict_types=1);

namespace App\Infrastructure\Payment;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Service\PaymentStatusServiceInterface;

final class MockPaymentStatusService implements PaymentStatusServiceInterface
{
    public function checkPaymentStatus(Order $order): bool
    {
        // For individual orders: respect the database state
        // If order is not paid in DB, payment service should return false
        // (external microservice would have updated DB if payment was made)
        if ($order->getContractorType()->isIndividual()) {
            return $order->isPaid();
        }

        // For legal entity orders: return random true/false (mock behavior)
        // In production, this would call the actual payment microservice
        return (bool) random_int(0, 1);
    }
}
