<?php

declare(strict_types=1);

namespace App\Presentation\Controller\Order;

use App\Application\Order\Query\CheckOrderCompletionHandler;
use App\Presentation\Request\CheckOrderCompletionRequest;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Orders', description: 'Order management endpoints')]
final class CheckOrderCompletionController extends AbstractController
{
    public function __construct(
        private readonly CheckOrderCompletionHandler $handler
    ) {
    }

    #[Route('/api/orders/{uniqueOrderNumber}/complete', name: 'api_orders_check_completion', methods: ['GET'])]
    #[OA\Get(
        path: '/api/orders/{uniqueOrderNumber}/complete',
        summary: 'Check order payment completion status',
        description: 'Checks whether an order has been paid. For individual contractors, the payment status is stored in the database. For legal entities, the payment status is checked via a payment microservice.',
        tags: ['Orders'],
        parameters: [
            new OA\Parameter(
                name: 'uniqueOrderNumber',
                in: 'path',
                required: true,
                description: 'Unique order number in format YYYY-MM-NNNNN',
                schema: new OA\Schema(
                    type: 'string',
                    pattern: '^\d{4}-\d{2}-\d+$',
                    example: '2025-11-1'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Order payment status retrieved successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'isPaid',
                            type: 'boolean',
                            description: 'Whether the order has been paid',
                            example: true
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            description: 'Status message',
                            example: 'Order has been paid successfully. Thank you for your purchase!'
                        ),
                    ]
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
                                    property: 'uniqueOrderNumber',
                                    type: 'string',
                                    example: 'Unique order number must be in format YYYY-MM-NNNNN (e.g., 2020-09-12345)'
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Order not found',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Order not found'
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
    public function __invoke(CheckOrderCompletionRequest $request): JsonResponse
    {
        $query = $request->createQuery();
        $responseDTO = $this->handler->handle($query);

        return new JsonResponse(
            [
                'isPaid' => $responseDTO->isPaid,
                'message' => $responseDTO->message->getValue(),
            ],
            Response::HTTP_OK
        );
    }
}

