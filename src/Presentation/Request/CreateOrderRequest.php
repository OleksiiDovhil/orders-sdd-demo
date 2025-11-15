<?php

declare(strict_types=1);

namespace App\Presentation\Request;

use App\Application\Order\Command\CreateOrderCommand;
use App\Application\Order\DTO\CreateOrderItemDTO;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    schema: 'CreateOrderRequest',
    type: 'object',
    required: ['sum', 'contractorType', 'items'],
    description: 'Request body for creating a new order'
)]
final readonly class CreateOrderRequest
{
    /**
     * @param CreateOrderItemRequest[] $items
     */
    public function __construct(
        #[OA\Property(
            property: 'sum',
            type: 'integer',
            description: 'Total order amount in cents',
            example: 1000
        )]
        #[Assert\NotBlank(message: 'Sum is required')]
        #[Assert\Type(type: 'integer', message: 'Sum must be an integer')]
        #[Assert\GreaterThanOrEqual(value: 0, message: 'Sum must be non-negative')]
        public int $sum,
        #[OA\Property(
            property: 'contractorType',
            type: 'integer',
            description: 'Contractor type: 1 = individual, 2 = legal entity',
            enum: [1, 2],
            example: 1
        )]
        #[Assert\NotBlank(message: 'Contractor type is required')]
        #[Assert\Type(type: 'integer', message: 'Contractor type must be an integer')]
        #[Assert\Choice(choices: [1, 2], message: 'Contractor type must be 1 (individual) or 2 (legal entity)')]
        public int $contractorType,
        #[OA\Property(
            property: 'items',
            type: 'array',
            description: 'Array of order items',
            items: new OA\Items(
                ref: '#/components/schemas/OrderItem'
            )
        )]
        #[Assert\NotBlank(message: 'Items are required')]
        #[Assert\Type(type: 'array', message: 'Items must be an array')]
        #[Assert\Count(min: 1, minMessage: 'At least one item is required')]
        #[Assert\All([
            new Assert\Type(CreateOrderItemRequest::class),
        ])]
        public array $items
    ) {
    }

    public function createCommand(): CreateOrderCommand
    {
        $itemDTOs = array_map(
            fn (CreateOrderItemRequest $item) => new CreateOrderItemDTO(
                $item->productId,
                $item->price,
                $item->quantity
            ),
            $this->items
        );

        return new CreateOrderCommand(
            $this->sum,
            $this->contractorType,
            $itemDTOs
        );
    }
}
