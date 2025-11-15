<?php

declare(strict_types=1);

namespace App\Presentation\Request;

use App\Application\Order\Command\CreateOrderCommand;
use App\Application\Order\DTO\CreateOrderItemDTO;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateOrderRequest
{
    /**
     * @param CreateOrderItemRequest[] $items
     */
    public function __construct(
        #[Assert\NotBlank(message: 'Sum is required')]
        #[Assert\Type(type: 'integer', message: 'Sum must be an integer')]
        #[Assert\GreaterThanOrEqual(value: 0, message: 'Sum must be non-negative')]
        public int $sum,

        #[Assert\NotBlank(message: 'Contractor type is required')]
        #[Assert\Type(type: 'integer', message: 'Contractor type must be an integer')]
        #[Assert\Choice(choices: [1, 2], message: 'Contractor type must be 1 (individual) or 2 (legal entity)')]
        public int $contractorType,

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

