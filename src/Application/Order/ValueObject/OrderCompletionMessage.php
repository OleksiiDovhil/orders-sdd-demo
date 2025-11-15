<?php

declare(strict_types=1);

namespace App\Application\Order\ValueObject;

enum OrderCompletionMessage: string
{
    case PAID = 'Order has been paid successfully. Thank you for your purchase!';
    case PENDING = 'Order payment is pending. Please complete the payment to proceed.';

    public function getValue(): string
    {
        return $this->value;
    }
}
