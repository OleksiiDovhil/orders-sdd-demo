<?php

declare(strict_types=1);

namespace App\Domain\Order\Service;

use App\Domain\Order\ValueObject\ContractorType;
use App\Domain\Order\ValueObject\UniqueOrderNumber;

final class RedirectUrlGenerator
{
    public function __construct(
        private readonly string $paymentAggregatorBaseUrl
    ) {
    }

    public function generate(UniqueOrderNumber $uniqueOrderNumber, ContractorType $contractorType): string
    {
        $baseUrl = rtrim($this->paymentAggregatorBaseUrl, '/');
        $orderNumber = $uniqueOrderNumber->getValue();

        if ($contractorType->isIndividual()) {
            return sprintf('%s/pay/%s', $baseUrl, $orderNumber);
        }

        return sprintf('%s/orders/%s/bill', $baseUrl, $orderNumber);
    }
}
