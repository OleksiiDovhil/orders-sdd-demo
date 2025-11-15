<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Request\ValueResolver;

use App\Presentation\Request\CheckOrderCompletionRequest;
use App\Presentation\Request\RequestDeserializer;
use App\Presentation\Request\ValueResolver\RequestValueResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validation;

final class RequestValueResolverTest extends TestCase
{
    private RequestDeserializer $requestDeserializer;
    private RequestValueResolver $resolver;

    protected function setUp(): void
    {
        $serializer = $this->createMock(SerializerInterface::class);
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
        $this->requestDeserializer = new RequestDeserializer($serializer, $validator);
        $this->resolver = new RequestValueResolver($this->requestDeserializer);
    }

    public function testShouldResolveWithValidRequestDTOClass(): void
    {
        // Arrange
        $request = new Request();
        $request->attributes->set('uniqueOrderNumber', '2025-11-12345');
        $argument = $this->createArgumentMetadata(CheckOrderCompletionRequest::class);

        // Act
        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        // Assert
        $this->assertCount(1, $result);
        $this->assertInstanceOf(CheckOrderCompletionRequest::class, $result[0]);
        $this->assertEquals('2025-11-12345', $result[0]->uniqueOrderNumber);
    }

    public function testShouldReturnEmptyArrayWhenTypeIsNull(): void
    {
        // Arrange
        $request = new Request();
        $argument = $this->createArgumentMetadata(null);

        // Act
        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        // Assert
        $this->assertEmpty($result);
    }

    public function testShouldReturnEmptyArrayWhenClassDoesNotExist(): void
    {
        // Arrange
        $request = new Request();
        $argument = $this->createArgumentMetadata('NonExistent\\Class\\Name');

        // Act
        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        // Assert
        $this->assertEmpty($result);
    }

    public function testShouldReturnEmptyArrayWhenClassNotInRequestNamespace(): void
    {
        // Arrange
        $request = new Request();
        $argument = $this->createArgumentMetadata(\stdClass::class);

        // Act
        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        // Assert
        $this->assertEmpty($result);
    }

    public function testShouldReturnEmptyArrayWhenTypeIsBaseRequestClass(): void
    {
        // Arrange
        $request = new Request();
        $argument = $this->createArgumentMetadata(Request::class);

        // Act
        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        // Assert
        $this->assertEmpty($result);
    }

    public function testShouldBubbleUpException(): void
    {
        // Arrange
        $request = new Request();
        // Missing uniqueOrderNumber will cause exception
        $argument = $this->createArgumentMetadata(CheckOrderCompletionRequest::class);

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('uniqueOrderNumber path parameter is required');

        // Act
        iterator_to_array($this->resolver->resolve($request, $argument));
    }

    private function createArgumentMetadata(?string $type): ArgumentMetadata
    {
        return new ArgumentMetadata('test', $type, false, false, null);
    }
}
