<?php

declare(strict_types=1);

namespace App\Domain\Order\Service;

use App\Domain\Order\Entity\Order;

interface PaymentStatusServiceInterface
{
    public function checkPaymentStatus(Order $order): bool;
}

