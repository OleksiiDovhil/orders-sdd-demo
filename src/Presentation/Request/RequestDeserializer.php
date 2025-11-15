<?php

declare(strict_types=1);

namespace App\Presentation\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class RequestDeserializer
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {
    }

    /**
     * @template T of object
     * @param Request $request
     * @param class-string<T> $class
     * @return T
     * @throws ValidationFailedException
     */
    public function deserializeAndValidate(Request $request, string $class): object
    {
        // Handle path parameters (e.g., for CheckOrderCompletionRequest)
        if ($class === CheckOrderCompletionRequest::class) {
            return $this->deserializeCheckOrderCompletionRequest($request);
        }

        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON format');
        }

        // For nested arrays, we need to manually construct the objects
        // since readonly classes with constructor properties aren't easily denormalizable
        if ($class === CreateOrderRequest::class) {
            return $this->deserializeCreateOrderRequest($data);
        }

        // Fallback to serializer for other types
        $object = $this->serializer->deserialize(
            $request->getContent(),
            $class,
            'json'
        );

        $violations = $this->validator->validate($object);
        if (count($violations) > 0) {
            throw new ValidationFailedException($object, $violations);
        }

        return $object;
    }

    private function deserializeCreateOrderRequest(array $data): CreateOrderRequest
    {
        $items = [];
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemData) {
                $items[] = new CreateOrderItemRequest(
                    $itemData['productId'] ?? 0,
                    $itemData['price'] ?? 0,
                    $itemData['quantity'] ?? 0
                );
            }
        }

        $request = new CreateOrderRequest(
            $data['sum'] ?? 0,
            $data['contractorType'] ?? 0,
            $items
        );

        $violations = $this->validator->validate($request);
        if (count($violations) > 0) {
            throw new ValidationFailedException($request, $violations);
        }

        return $request;
    }

    private function deserializeCheckOrderCompletionRequest(Request $request): CheckOrderCompletionRequest
    {
        $uniqueOrderNumber = $request->attributes->get('uniqueOrderNumber');

        if ($uniqueOrderNumber === null) {
            throw new \InvalidArgumentException('uniqueOrderNumber path parameter is required');
        }

        $requestObject = new CheckOrderCompletionRequest((string) $uniqueOrderNumber);

        $violations = $this->validator->validate($requestObject);
        if (count($violations) > 0) {
            throw new ValidationFailedException($requestObject, $violations);
        }

        return $requestObject;
    }
}

