<?php

declare(strict_types=1);

namespace App\Presentation\Controller\Order;

use App\Application\Order\Query\GetRecentOrdersHandler;
use App\Presentation\Request\GetRecentOrdersRequest;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Orders', description: 'Order management endpoints')]
final class GetRecentOrdersController extends AbstractController
{
    public function __construct(
        private readonly GetRecentOrdersHandler $handler
    ) {
    }

    #[Route(
        '/api/orders',
        name: 'api_orders_get_recent',
        methods: ['GET']
    )]
    #[OA\Get(
        path: '/api/orders',
        summary: 'Get recent orders',
        description: 'Returns the specified number of most recent orders, '
            . 'ordered by creation date (most recent first).',
        tags: ['Orders'],
        parameters: [
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                required: true,
                description: 'Maximum number of orders to return',
                schema: new OA\Schema(
                    type: 'integer',
                    minimum: 1,
                    maximum: 1000,
                    example: 5
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Recent orders retrieved successfully',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(
                                property: 'id',
                                type: 'string',
                                description: 'Unique order number in format YYYY-MM-NNNNN',
                                example: '2025-11-1'
                            ),
                            new OA\Property(
                                property: 'sum',
                                type: 'integer',
                                description: 'Total order amount in cents',
                                example: 1000
                            ),
                            new OA\Property(
                                property: 'contractorType',
                                type: 'integer',
                                description: 'Contractor type: 1 = individual, 2 = legal entity',
                                enum: [1, 2],
                                example: 1
                            ),
                            new OA\Property(
                                property: 'items',
                                type: 'array',
                                description: 'Order items',
                                items: new OA\Items(
                                    type: 'object',
                                    properties: [
                                        new OA\Property(
                                            property: 'productId',
                                            type: 'integer',
                                            description: 'Product ID',
                                            example: 123
                                        ),
                                        new OA\Property(
                                            property: 'price',
                                            type: 'integer',
                                            description: 'Price per unit in cents',
                                            example: 500
                                        ),
                                        new OA\Property(
                                            property: 'quantity',
                                            type: 'integer',
                                            description: 'Quantity',
                                            example: 2
                                        ),
                                    ]
                                )
                            ),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request - validation error',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            description: 'Validation errors',
                            properties: [
                                new OA\Property(
                                    property: 'limit',
                                    type: 'string',
                                    example: 'Limit is required'
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Internal server error'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(GetRecentOrdersRequest $request): JsonResponse
    {
        $query = $request->createQuery();
        $responseDTO = $this->handler->handle($query);

        // Convert DTOs to array format for JSON response
        $orders = array_map(
            fn ($orderDTO) => [
                'id' => $orderDTO->id,
                'sum' => $orderDTO->sum,
                'contractorType' => $orderDTO->contractorType,
                'items' => array_map(
                    fn ($itemDTO) => [
                        'productId' => $itemDTO->productId,
                        'price' => $itemDTO->price,
                        'quantity' => $itemDTO->quantity,
                    ],
                    $orderDTO->items
                ),
            ],
            $responseDTO->orders
        );

        return new JsonResponse(
            $orders,
            Response::HTTP_OK
        );
    }
}
