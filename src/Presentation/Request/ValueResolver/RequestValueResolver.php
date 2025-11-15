<?php

declare(strict_types=1);

namespace App\Presentation\Request\ValueResolver;

use App\Presentation\Request\RequestDeserializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class RequestValueResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly RequestDeserializer $requestDeserializer
    ) {
    }

    /**
     * @return iterable<object>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        // Only resolve if it's a request DTO class
        if ($type === null || !class_exists($type) || !str_starts_with($type, 'App\\Presentation\\Request\\')) {
            return [];
        }

        // Skip if it's the base Request class
        if ($type === Request::class) {
            return [];
        }

        try {
            $requestObject = $this->requestDeserializer->deserializeAndValidate($request, $type);
            yield $requestObject;
        } catch (\Exception $e) {
            // Let the exception bubble up - Symfony will handle it
            throw $e;
        }
    }
}

