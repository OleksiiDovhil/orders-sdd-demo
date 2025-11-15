<?php

declare(strict_types=1);

namespace App\Presentation\Request;

use App\Application\Order\Query\CheckOrderCompletionQuery;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CheckOrderCompletionRequest
{
    public function __construct(
        #[OA\Property(
            property: 'uniqueOrderNumber',
            type: 'string',
            description: 'Unique order number in format YYYY-MM-NNNNN',
            pattern: '^\d{4}-\d{2}-\d+$',
            example: '2025-11-12345'
        )]
        #[Assert\NotBlank(message: 'Unique order number is required')]
        #[Assert\Type(type: 'string', message: 'Unique order number must be a string')]
        #[Assert\Regex(
            pattern: '/^\d{4}-\d{2}-\d+$/',
            message: 'Unique order number must be in format YYYY-MM-NNNNN (e.g., 2020-09-12345)'
        )]
        public string $uniqueOrderNumber
    ) {
    }

    public function createQuery(): CheckOrderCompletionQuery
    {
        return new CheckOrderCompletionQuery($this->uniqueOrderNumber);
    }
}
