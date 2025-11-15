<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Request;

use App\Presentation\Request\CheckOrderCompletionRequest;
use App\Presentation\Request\RequestDeserializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Validation;

final class RequestDeserializerTest extends TestCase
{
    private RequestDeserializer $deserializer;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
        $this->deserializer = new RequestDeserializer($this->serializer, $this->validator);
    }

    public function testShouldDeserializeCheckOrderCompletionRequestFromPathParameter(): void
    {
        // Arrange
        $request = new Request();
        $request->attributes->set('uniqueOrderNumber', '2025-11-12345');

        // Act
        $result = $this->deserializer->deserializeAndValidate($request, CheckOrderCompletionRequest::class);

        // Assert
        $this->assertInstanceOf(CheckOrderCompletionRequest::class, $result);
        $this->assertEquals('2025-11-12345', $result->uniqueOrderNumber);
    }

    public function testShouldThrowExceptionWhenPathParameterIsMissing(): void
    {
        // Arrange
        $request = new Request();

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('uniqueOrderNumber path parameter is required');

        // Act
        $this->deserializer->deserializeAndValidate($request, CheckOrderCompletionRequest::class);
    }

    public function testShouldThrowValidationExceptionWhenPathParameterFormatIsInvalid(): void
    {
        // Arrange
        $request = new Request();
        $request->attributes->set('uniqueOrderNumber', 'invalid-format');

        // Assert
        $this->expectException(\Symfony\Component\Validator\Exception\ValidationFailedException::class);

        // Act
        $this->deserializer->deserializeAndValidate($request, CheckOrderCompletionRequest::class);
    }
}

