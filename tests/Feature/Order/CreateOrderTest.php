<?php

declare(strict_types=1);

namespace App\Tests\Feature\Order;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CreateOrderTest extends WebTestCase
{
    protected static function createKernel(array $options = []): \Symfony\Component\HttpKernel\KernelInterface
    {
        return new \App\Kernel('test', true);
    }

    public function testShouldCreateOrderForIndividualContractor(): void
    {
        // Arrange
        $client = static::createClient();
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

        // Act
        $client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($requestData));

        // Assert
        $this->assertResponseStatusCodeSame(201);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('uniqueOrderNumber', $response);
        $this->assertArrayHasKey('redirectUrl', $response);
        $this->assertStringContainsString('/pay/', $response['redirectUrl']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d+$/', $response['uniqueOrderNumber']);
    }

    public function testShouldCreateOrderForLegalEntityContractor(): void
    {
        // Arrange
        $client = static::createClient();
        $requestData = [
            'sum' => 2000,
            'contractorType' => 2,
            'items' => [
                [
                    'productId' => 2,
                    'price' => 2000,
                    'quantity' => 1,
                ],
            ],
        ];

        // Act
        $client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($requestData));

        // Assert
        $this->assertResponseStatusCodeSame(201);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('uniqueOrderNumber', $response);
        $this->assertArrayHasKey('redirectUrl', $response);
        $this->assertStringContainsString('/orders/', $response['redirectUrl']);
        $this->assertStringContainsString('/bill', $response['redirectUrl']);
    }

    public function testShouldReturnBadRequestWhenContractorTypeIsInvalid(): void
    {
        // Arrange
        $client = static::createClient();
        $requestData = [
            'sum' => 1000,
            'contractorType' => 99,
            'items' => [
                [
                    'productId' => 1,
                    'price' => 1000,
                    'quantity' => 1,
                ],
            ],
        ];

        // Act
        $client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($requestData));

        // Assert
        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('contractorType', $response['errors']);
        $this->assertStringContainsString('must be 1 (individual) or 2 (legal entity)', $response['errors']['contractorType']);
    }

    public function testShouldReturnBadRequestWhenRequiredFieldsAreMissing(): void
    {
        // Arrange
        $client = static::createClient();
        $requestData = [
            'sum' => 1000,
            // Missing contractorType and items
        ];

        // Act
        $client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($requestData));

        // Assert
        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('contractorType', $response['errors']);
        $this->assertArrayHasKey('items', $response['errors']);
    }

    public function testShouldReturnBadRequestWhenSumIsNegative(): void
    {
        // Arrange
        $client = static::createClient();
        $requestData = [
            'sum' => -100,
            'contractorType' => 1,
            'items' => [
                [
                    'productId' => 1,
                    'price' => 1000,
                    'quantity' => 1,
                ],
            ],
        ];

        // Act
        $client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($requestData));

        // Assert
        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('sum', $response['errors']);
        $this->assertStringContainsString('non-negative', $response['errors']['sum']);
    }

    public function testShouldReturnBadRequestWhenItemsArrayIsEmpty(): void
    {
        // Arrange
        $client = static::createClient();
        $requestData = [
            'sum' => 1000,
            'contractorType' => 1,
            'items' => [],
        ];

        // Act
        $client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($requestData));

        // Assert
        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('items', $response['errors']);
        $this->assertStringContainsString('At least one item is required', $response['errors']['items']);
    }

    public function testShouldReturnBadRequestWhenItemProductIdIsMissing(): void
    {
        // Arrange
        $client = static::createClient();
        $requestData = [
            'sum' => 1000,
            'contractorType' => 1,
            'items' => [
                [
                    // Missing productId
                    'price' => 1000,
                    'quantity' => 1,
                ],
            ],
        ];

        // Act
        $client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($requestData));

        // Assert
        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($client->getResponse()->getContent(), true);
        // Response can have either 'errors' (validation) or 'error' (domain exception)
        $this->assertTrue(
            isset($response['errors']) || isset($response['error']),
            'Response should have either "errors" or "error" key'
        );
    }

    public function testShouldReturnBadRequestWhenItemProductIdIsNegative(): void
    {
        // Arrange
        $client = static::createClient();
        $requestData = [
            'sum' => 1000,
            'contractorType' => 1,
            'items' => [
                [
                    'productId' => -1,
                    'price' => 1000,
                    'quantity' => 1,
                ],
            ],
        ];

        // Act
        $client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($requestData));

        // Assert
        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($client->getResponse()->getContent(), true);
        // Response can have either 'errors' (validation) or 'error' (domain exception)
        $this->assertTrue(
            isset($response['errors']) || isset($response['error']),
            'Response should have either "errors" or "error" key'
        );
    }

    public function testShouldReturnBadRequestWhenItemPriceIsNegative(): void
    {
        // Arrange
        $client = static::createClient();
        $requestData = [
            'sum' => 1000,
            'contractorType' => 1,
            'items' => [
                [
                    'productId' => 1,
                    'price' => -100,
                    'quantity' => 1,
                ],
            ],
        ];

        // Act
        $client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($requestData));

        // Assert
        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($client->getResponse()->getContent(), true);
        // Response can have either 'errors' (validation) or 'error' (domain exception)
        $this->assertTrue(
            isset($response['errors']) || isset($response['error']),
            'Response should have either "errors" or "error" key'
        );
    }

    public function testShouldReturnBadRequestWhenItemQuantityIsZero(): void
    {
        // Arrange
        $client = static::createClient();
        $requestData = [
            'sum' => 1000,
            'contractorType' => 1,
            'items' => [
                [
                    'productId' => 1,
                    'price' => 1000,
                    'quantity' => 0,
                ],
            ],
        ];

        // Act
        $client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($requestData));

        // Assert
        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($client->getResponse()->getContent(), true);
        // Response can have either 'errors' (validation) or 'error' (domain exception)
        $this->assertTrue(
            isset($response['errors']) || isset($response['error']),
            'Response should have either "errors" or "error" key'
        );
        // If it's an error, it should mention quantity
        if (isset($response['error'])) {
            $this->assertStringContainsStringIgnoringCase('quantity', $response['error']);
        }
    }

    public function testShouldReturnBadRequestWhenInvalidJsonFormat(): void
    {
        // Arrange
        $client = static::createClient();

        // Act
        $client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{invalid json}');

        // Assert
        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertStringContainsString('Invalid JSON', $response['error']);
    }

}

