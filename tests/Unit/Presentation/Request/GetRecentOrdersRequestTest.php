<?php

declare(strict_types=1);

namespace App\Tests\Unit\Presentation\Request;

use App\Application\Order\Query\GetRecentOrdersQuery;
use App\Presentation\Request\GetRecentOrdersRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class GetRecentOrdersRequestTest extends TestCase
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
        $request = new GetRecentOrdersRequest(5);

        // Act
        $query = $request->createQuery();

        // Assert
        $this->assertInstanceOf(GetRecentOrdersQuery::class, $query);
        $this->assertEquals(5, $query->limit);
    }

    public function testShouldPassValidationWithValidLimit(): void
    {
        // Arrange
        $request = new GetRecentOrdersRequest(10);

        // Act
        $violations = $this->validator->validate($request);

        // Assert
        $this->assertCount(0, $violations);
    }

    public function testShouldFailValidationWhenLimitIsZero(): void
    {
        // Arrange
        $request = new GetRecentOrdersRequest(0);

        // Act
        $violations = $this->validator->validate($request);

        // Assert
        $this->assertCount(1, $violations);
        $violation = $violations[0] ?? null;
        if ($violation !== null) {
            $this->assertEquals('limit', $violation->getPropertyPath());
            $message = $violation->getMessage();
            if (is_string($message)) {
                $this->assertStringContainsString('greater than', $message);
            }
        }
    }

    public function testShouldFailValidationWhenLimitIsNegative(): void
    {
        // Arrange
        $request = new GetRecentOrdersRequest(-1);

        // Act
        $violations = $this->validator->validate($request);

        // Assert
        $this->assertCount(1, $violations);
        $violation = $violations[0] ?? null;
        if ($violation !== null) {
            $this->assertEquals('limit', $violation->getPropertyPath());
        }
    }

    public function testShouldFailValidationWhenLimitExceedsMaximum(): void
    {
        // Arrange
        $request = new GetRecentOrdersRequest(1001);

        // Act
        $violations = $this->validator->validate($request);

        // Assert
        $this->assertCount(1, $violations);
        $violation = $violations[0] ?? null;
        if ($violation !== null) {
            $this->assertEquals('limit', $violation->getPropertyPath());
            $message = $violation->getMessage();
            if (is_string($message)) {
                $this->assertStringContainsString('not exceed', $message);
            }
        }
    }

    public function testShouldPassValidationWithMaximumLimit(): void
    {
        // Arrange
        $request = new GetRecentOrdersRequest(1000);

        // Act
        $violations = $this->validator->validate($request);

        // Assert
        $this->assertCount(0, $violations);
    }

    public function testShouldPassValidationWithMinimumLimit(): void
    {
        // Arrange
        $request = new GetRecentOrdersRequest(1);

        // Act
        $violations = $this->validator->validate($request);

        // Assert
        $this->assertCount(0, $violations);
    }
}
