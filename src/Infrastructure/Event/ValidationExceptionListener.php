<?php

declare(strict_types=1);

namespace App\Infrastructure\Event;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
final class ValidationExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ValidationFailedException) {
            $errors = [];
            foreach ($exception->getViolations() as $violation) {
                $propertyPath = $violation->getPropertyPath();
                $errors[$propertyPath] = $violation->getMessage();
            }

            $response = new JsonResponse(
                ['errors' => $errors],
                JsonResponse::HTTP_BAD_REQUEST
            );

            $event->setResponse($response);
            return;
        }

        if ($exception instanceof \InvalidArgumentException) {
            $message = $exception->getMessage();
            if (str_contains($message, 'Invalid JSON')) {
                $response = new JsonResponse(
                    ['error' => $message],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            } else {
                // Handle domain validation errors (e.g., from OrderItem, Order entities)
                $response = new JsonResponse(
                    ['error' => $message],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $event->setResponse($response);
            return;
        }

        if ($exception instanceof NotFoundHttpException) {
            $response = new JsonResponse(
                ['error' => $exception->getMessage()],
                JsonResponse::HTTP_NOT_FOUND
            );

            $event->setResponse($response);
        }
    }
}

