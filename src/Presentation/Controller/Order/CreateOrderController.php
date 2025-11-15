<?php

declare(strict_types=1);

namespace App\Presentation\Controller\Order;

use App\Application\Order\Command\CreateOrderHandler;
use App\Domain\Order\Service\RedirectUrlGenerator;
use App\Domain\Order\ValueObject\ContractorType;
use App\Domain\Order\ValueObject\UniqueOrderNumber;
use App\Presentation\Request\CreateOrderRequest;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Orders', description: 'Order management endpoints')]
final class CreateOrderController extends AbstractController
{
    public function __construct(
        private readonly CreateOrderHandler $handler,
        private readonly RedirectUrlGenerator $redirectUrlGenerator
    ) {
    }

    #[Route('/api/orders', name: 'api_orders_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/orders',
        summary: 'Create a new order',
        description: 'Creates a new order with the provided items. The system generates a unique order number '
            . 'in the format {year}-{month}-{sequentialOrderNumber} and returns a redirect URL '
            . 'based on the contractor type.',
        tags: ['Orders'],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Order creation data',
            content: new OA\JsonContent(
                type: 'object',
                required: ['sum', 'contractorType', 'items'],
                properties: [
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
                        description: 'Array of order items',
                        items: new OA\Items(
                            type: 'object',
                            required: ['productId', 'price', 'quantity'],
                            properties: [
                                new OA\Property(
                                    property: 'productId',
                                    type: 'integer',
                                    description: 'Product identifier',
                                    example: 1
                                ),
                                new OA\Property(
                                    property: 'price',
                                    type: 'integer',
                                    description: 'Product price in cents',
                                    example: 1000
                                ),
                                new OA\Property(
                                    property: 'quantity',
                                    type: 'integer',
                                    description: 'Product quantity',
                                    minimum: 1,
                                    example: 1
                                ),
                            ]
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Order created successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'uniqueOrderNumber',
                            type: 'string',
                            description: 'Unique order number in format {year}-{month}-{sequentialOrderNumber}',
                            example: '2025-11-1'
                        ),
                        new OA\Property(
                            property: 'redirectUrl',
                            type: 'string',
                            description: 'Redirect URL for payment. For individuals: /pay/{uniqueOrderNumber}, '
                                . 'for legal entities: /orders/{uniqueOrderNumber}/bill',
                            example: 'http://some-pay-agregator.com/pay/2025-11-1'
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
                            property: 'error',
                            type: 'string',
                            example: 'Missing required fields: sum, contractorType, items'
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
    public function __invoke(CreateOrderRequest $createOrderRequest): JsonResponse
    {
        // Create command from request
        $command = $createOrderRequest->createCommand();

        // Handle command
        $responseDTO = $this->handler->handle($command);

        // Generate redirect URL
        $redirectUrl = $this->redirectUrlGenerator->generate(
            new UniqueOrderNumber($responseDTO->uniqueOrderNumber),
            ContractorType::fromInt($command->contractorType)
        );

        return new JsonResponse(
            [
                'uniqueOrderNumber' => $responseDTO->uniqueOrderNumber,
                'redirectUrl' => $redirectUrl,
            ],
            Response::HTTP_CREATED
        );
    }
}
