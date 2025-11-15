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
            $result = $this->deserializeCheckOrderCompletionRequest($request);
            // @phpstan-ignore-next-line - Generic type T is correctly narrowed by class check
            return $result;
        }

        $content = $request->getContent();
        if ($content === '') {
            $data = [];
        } else {
            $data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON format');
            }
            if (!is_array($data)) {
                throw new \InvalidArgumentException('JSON must decode to an array');
            }
        }

        // For nested arrays, we need to manually construct the objects
        // since readonly classes with constructor properties aren't easily denormalizable
        if ($class === CreateOrderRequest::class) {
            $result = $this->deserializeCreateOrderRequest($data);
            // @phpstan-ignore-next-line - Generic type T is correctly narrowed by class check
            return $result;
        }

        // Fallback to serializer for other types
        $object = $this->serializer->deserialize(
            $content,
            $class,
            'json'
        );

        $violations = $this->validator->validate($object);
        if (count($violations) > 0) {
            throw new ValidationFailedException($object, $violations);
        }

        /** @var T */
        return $object;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function deserializeCreateOrderRequest(array $data): CreateOrderRequest
    {
        $items = [];
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemData) {
                if (!is_array($itemData)) {
                    continue;
                }
                $items[] = new CreateOrderItemRequest(
                    isset($itemData['productId']) && is_numeric($itemData['productId']) ? (int) $itemData['productId'] : 0,
                    isset($itemData['price']) && is_numeric($itemData['price']) ? (int) $itemData['price'] : 0,
                    isset($itemData['quantity']) && is_numeric($itemData['quantity']) ? (int) $itemData['quantity'] : 0
                );
            }
        }

        $sum = isset($data['sum']) && is_numeric($data['sum']) ? (int) $data['sum'] : 0;
        $contractorType = isset($data['contractorType']) && is_numeric($data['contractorType']) ? (int) $data['contractorType'] : 0;
        $request = new CreateOrderRequest(
            $sum,
            $contractorType,
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

        if (!is_string($uniqueOrderNumber)) {
            throw new \InvalidArgumentException('uniqueOrderNumber must be a string');
        }

        $requestObject = new CheckOrderCompletionRequest($uniqueOrderNumber);

        $violations = $this->validator->validate($requestObject);
        if (count($violations) > 0) {
            throw new ValidationFailedException($requestObject, $violations);
        }

        return $requestObject;
    }
}

