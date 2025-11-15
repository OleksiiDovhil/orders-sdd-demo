<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Request;

use App\Presentation\Request\CheckOrderCompletionRequest;
use App\Presentation\Request\CreateOrderRequest;
use App\Presentation\Request\RequestDeserializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Validation;

final class RequestDeserializerTest extends TestCase
{
    private RequestDeserializer $deserializer;
    private SerializerInterface&\PHPUnit\Framework\MockObject\MockObject $serializer;
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
        $this->expectException(ValidationFailedException::class);

        // Act
        $this->deserializer->deserializeAndValidate($request, CheckOrderCompletionRequest::class);
    }

    public function testShouldDeserializeAndValidateCreateOrderRequest(): void
    {
        // Arrange
        $requestData = [
            'sum' => 1000,
            'contractorType' => 1,
            'items' => [
                [
                    'productId' => 1,
                    'price' => 1000,
                    'quantity' => 1,
                ],
            ],
        ];
        $request = new Request();
        $jsonContent = json_encode($requestData);
        if ($jsonContent === false) {
            $this->fail('Failed to encode request data to JSON');
        }
        $request->initialize([], [], [], [], [], [], $jsonContent);

        // Act
        $result = $this->deserializer->deserializeAndValidate($request, CreateOrderRequest::class);

        // Assert
        $this->assertInstanceOf(CreateOrderRequest::class, $result);
        $this->assertEquals(1000, $result->sum);
        $this->assertEquals(1, $result->contractorType);
        $this->assertCount(1, $result->items);
    }

    public function testShouldDeserializeAndValidateWithEmptyJSONContent(): void
    {
        // Arrange
        $request = new Request();
        $request->initialize([], [], [], [], [], [], '');

        // Assert - Empty JSON should create request but validation will fail
        $this->expectException(ValidationFailedException::class);

        // Act
        $this->deserializer->deserializeAndValidate($request, CreateOrderRequest::class);
    }

    public function testShouldThrowExceptionWhenJSONFormatIsInvalid(): void
    {
        // Arrange
        $request = new Request();
        $request->initialize([], [], [], [], [], [], 'invalid json{');

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JSON format');

        // Act
        $this->deserializer->deserializeAndValidate($request, CreateOrderRequest::class);
    }

    public function testShouldThrowExceptionWhenJSONDoesNotDecodeToArray(): void
    {
        // Arrange
        $request = new Request();
        $request->initialize([], [], [], [], [], [], '"just a string"');

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('JSON must decode to an array');

        // Act
        $this->deserializer->deserializeAndValidate($request, CreateOrderRequest::class);
    }

    public function testShouldUseFallbackSerializerForOtherTypes(): void
    {
        // Arrange
        $request = new Request();
        $request->initialize([], [], [], [], [], [], '{"test": "value"}');
        $mockObject = new \stdClass();
        $mockObject->test = 'value';

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with('{"test": "value"}', \stdClass::class, 'json')
            ->willReturn($mockObject);

        // Act
        $result = $this->deserializer->deserializeAndValidate($request, \stdClass::class);

        // Assert
        $this->assertInstanceOf(\stdClass::class, $result);
    }

    public function testShouldThrowValidationExceptionInFallbackSerializerPath(): void
    {
        // Arrange
        $request = new Request();
        $request->initialize([], [], [], [], [], [], '{"test": "value"}');
        $mockObject = new \stdClass();
        $mockObject->test = 'value';

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with('{"test": "value"}', \stdClass::class, 'json')
            ->willReturn($mockObject);

        // Mock validator to return violations
        $validator = $this->createMock(\Symfony\Component\Validator\Validator\ValidatorInterface::class);
        $violations = $this->createMock(\Symfony\Component\Validator\ConstraintViolationListInterface::class);
        $violations->expects($this->once())
            ->method('count')
            ->willReturn(1);
        $validator->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $deserializer = new RequestDeserializer($this->serializer, $validator);

        // Assert
        $this->expectException(ValidationFailedException::class);

        // Act
        $deserializer->deserializeAndValidate($request, \stdClass::class);
    }

    public function testShouldThrowValidationExceptionWhenValidationFails(): void
    {
        // Arrange
        $requestData = [
            'sum' => -1000, // Invalid: negative sum
            'contractorType' => 1,
            'items' => [],
        ];
        $request = new Request();
        $jsonContent = json_encode($requestData);
        if ($jsonContent === false) {
            $this->fail('Failed to encode request data to JSON');
        }
        $request->initialize([], [], [], [], [], [], $jsonContent);

        // Assert
        $this->expectException(ValidationFailedException::class);

        // Act
        $this->deserializer->deserializeAndValidate($request, CreateOrderRequest::class);
    }

    public function testShouldDeserializeCreateOrderRequestWithInvalidItemData(): void
    {
        // Arrange
        $requestData = [
            'sum' => 1000,
            'contractorType' => 1,
            'items' => [
                [
                    'productId' => 'invalid', // Invalid: not numeric
                    'price' => 'invalid',
                    'quantity' => 'invalid',
                ],
                'not an array', // Invalid: not an array
            ],
        ];
        $request = new Request();
        $jsonContent = json_encode($requestData);
        if ($jsonContent === false) {
            $this->fail('Failed to encode request data to JSON');
        }
        $request->initialize([], [], [], [], [], [], $jsonContent);

        // Act
        $result = $this->deserializer->deserializeAndValidate($request, CreateOrderRequest::class);

        // Assert
        $this->assertInstanceOf(CreateOrderRequest::class, $result);
        // Invalid items should be skipped or defaulted to 0
        $this->assertCount(1, $result->items);
    }

    public function testShouldThrowExceptionWhenUniqueOrderNumberIsNotString(): void
    {
        // Arrange
        $request = new Request();
        $request->attributes->set('uniqueOrderNumber', 12345); // Not a string

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('uniqueOrderNumber must be a string');

        // Act
        $this->deserializer->deserializeAndValidate($request, CheckOrderCompletionRequest::class);
    }
}
