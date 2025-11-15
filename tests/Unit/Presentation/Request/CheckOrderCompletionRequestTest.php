<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Request;

use App\Application\Order\Query\CheckOrderCompletionQuery;
use App\Presentation\Request\CheckOrderCompletionRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class CheckOrderCompletionRequestTest extends TestCase
{
    private \Symfony\Component\Validator\Validator\ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testShouldCreateQueryFromRequest(): void
    {
        // Arrange
        $request = new CheckOrderCompletionRequest('2025-11-1');

        // Act
        $query = $request->createQuery();

        // Assert
        $this->assertInstanceOf(CheckOrderCompletionQuery::class, $query);
        $this->assertEquals('2025-11-1', $query->uniqueOrderNumber);
    }

    public function testShouldPassValidationWithValidFormat(): void
    {
        // Arrange
        $request = new CheckOrderCompletionRequest('2025-11-12345');

        // Act
        $violations = $this->validator->validate($request);

        // Assert
        $this->assertCount(0, $violations);
    }

    public function testShouldFailValidationWhenEmpty(): void
    {
        // Arrange
        $request = new CheckOrderCompletionRequest('');

        // Act
        $violations = $this->validator->validate($request);

        // Assert
        $this->assertCount(1, $violations);
        $violation = $violations[0] ?? null;
        if ($violation !== null) {
            $this->assertEquals('uniqueOrderNumber', $violation->getPropertyPath());
            $message = $violation->getMessage();
            if (is_string($message)) {
                $this->assertStringContainsString('required', $message);
            }
        }
    }

    public function testShouldFailValidationWhenFormatIsInvalid(): void
    {
        // Arrange
        $request = new CheckOrderCompletionRequest('invalid-format');

        // Act
        $violations = $this->validator->validate($request);

        // Assert
        $this->assertCount(1, $violations);
        $violation = $violations[0] ?? null;
        if ($violation !== null) {
            $this->assertEquals('uniqueOrderNumber', $violation->getPropertyPath());
            $message = $violation->getMessage();
            if (is_string($message)) {
                $this->assertStringContainsString('format', $message);
            }
        }
    }

    public function testShouldFailValidationWhenFormatIsMissingYear(): void
    {
        // Arrange
        $request = new CheckOrderCompletionRequest('11-12345');

        // Act
        $violations = $this->validator->validate($request);

        // Assert
        $this->assertCount(1, $violations);
        $violation = $violations[0] ?? null;
        if ($violation !== null) {
            $this->assertEquals('uniqueOrderNumber', $violation->getPropertyPath());
        }
    }
}
