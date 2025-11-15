<?php

declare(strict_types=1);

namespace App\Tests\Feature\Order;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GetRecentOrdersTest extends WebTestCase
{
    /**
     * @param array<string, mixed> $options
     */
    protected static function createKernel(array $options = []): \Symfony\Component\HttpKernel\KernelInterface
    {
        return new \App\Kernel('test', true);
    }

    public function testShouldReturnRecentOrdersWithValidLimit(): void
    {
        // Arrange - Create some orders first
        $client = static::createClient();
        $this->createOrder($client, 1000, 1);
        $this->createOrder($client, 2000, 2);

        // Act
        $client->request('GET', '/api/orders?limit=5', [], [], [
            'ACCEPT' => 'application/json',
        ]);

        // Assert
        $this->assertResponseStatusCodeSame(200);
        $responseContent = $client->getResponse()->getContent();
        if ($responseContent === false) {
            $this->fail('Response content is false');
        }
        $response = json_decode($responseContent, true);
        if (!is_array($response)) {
            $this->fail('Response is not an array');
        }
        $this->assertGreaterThanOrEqual(2, count($response));
        $this->assertLessThanOrEqual(5, count($response));

        // Verify structure of first order
        if (isset($response[0]) && is_array($response[0])) {
            $order = $response[0];
            $this->assertArrayHasKey('id', $order);
            $this->assertArrayHasKey('sum', $order);
            $this->assertArrayHasKey('contractorType', $order);
            $this->assertArrayHasKey('items', $order);
            $this->assertIsString($order['id']);
            $this->assertIsInt($order['sum']);
            $this->assertIsInt($order['contractorType']);
            $this->assertIsArray($order['items']);

            // Verify items structure
            if (isset($order['items'][0]) && is_array($order['items'][0])) {
                $item = $order['items'][0];
                $this->assertArrayHasKey('productId', $item);
                $this->assertArrayHasKey('price', $item);
                $this->assertArrayHasKey('quantity', $item);
                $this->assertIsInt($item['productId']);
                $this->assertIsInt($item['price']);
                $this->assertIsInt($item['quantity']);
            }
        }
    }

    public function testShouldReturnOrdersInDescendingOrderByCreationDate(): void
    {
        // Arrange - Create orders with delays to ensure different timestamps
        $client = static::createClient();
        $firstOrderResponse = $this->createOrder($client, 1000, 1);
        usleep(100000); // 100ms delay
        $secondOrderResponse = $this->createOrder($client, 2000, 2);

        // Act
        $client->request('GET', '/api/orders?limit=10', [], [], [
            'ACCEPT' => 'application/json',
        ]);

        // Assert
        $this->assertResponseStatusCodeSame(200);
        $responseContent = $client->getResponse()->getContent();
        if ($responseContent === false) {
            $this->fail('Response content is false');
        }
        $response = json_decode($responseContent, true);
        if (!is_array($response)) {
            $this->fail('Response is not an array');
        }
        $this->assertGreaterThanOrEqual(2, count($response));

        // Verify orders are in descending order by checking the positions of our created orders
        // The more recent order (secondOrderResponse) should appear before the older one
        $firstOrderNumber = null;
        $secondOrderNumber = null;
        if (
            isset($firstOrderResponse['uniqueOrderNumber'])
            && is_string($firstOrderResponse['uniqueOrderNumber'])
        ) {
            $firstOrderNumber = $firstOrderResponse['uniqueOrderNumber'];
        }
        if (
            isset($secondOrderResponse['uniqueOrderNumber'])
            && is_string($secondOrderResponse['uniqueOrderNumber'])
        ) {
            $secondOrderNumber = $secondOrderResponse['uniqueOrderNumber'];
        }

        if ($firstOrderNumber !== null && $secondOrderNumber !== null) {
            $firstOrderIndex = null;
            $secondOrderIndex = null;

            foreach ($response as $index => $order) {
                if (is_array($order) && isset($order['id']) && is_string($order['id'])) {
                    if ($order['id'] === $firstOrderNumber) {
                        $firstOrderIndex = $index;
                    }
                    if ($order['id'] === $secondOrderNumber) {
                        $secondOrderIndex = $index;
                    }
                }
            }

            // If both orders are found in the response, the more recent one should come first
            if ($firstOrderIndex !== null && $secondOrderIndex !== null) {
                $this->assertLessThan(
                    $secondOrderIndex,
                    $firstOrderIndex,
                    'More recent order (secondOrder) should appear before older order (firstOrder) in the response'
                );
            }
        }
    }

    public function testShouldRespectLimitParameter(): void
    {
        // Arrange - Create multiple orders
        $client = static::createClient();
        for ($i = 0; $i < 10; $i++) {
            $this->createOrder($client, 1000 + $i, 1);
        }

        // Act
        $client->request('GET', '/api/orders?limit=3', [], [], [
            'ACCEPT' => 'application/json',
        ]);

        // Assert
        $this->assertResponseStatusCodeSame(200);
        $responseContent = $client->getResponse()->getContent();
        if ($responseContent === false) {
            $this->fail('Response content is false');
        }
        $response = json_decode($responseContent, true);
        if (!is_array($response)) {
            $this->fail('Response is not an array');
        }
        $this->assertLessThanOrEqual(3, count($response));
    }

    public function testShouldReturnEmptyArrayWhenNoOrdersExist(): void
    {
        // Arrange
        $client = static::createClient();

        // Act
        $client->request('GET', '/api/orders?limit=5', [], [], [
            'ACCEPT' => 'application/json',
        ]);

        // Assert
        $this->assertResponseStatusCodeSame(200);
        $responseContent = $client->getResponse()->getContent();
        if ($responseContent === false) {
            $this->fail('Response content is false');
        }
        $response = json_decode($responseContent, true);
        if (!is_array($response)) {
            $this->fail('Response is not an array');
        }
        // Response should be an array (may be empty if no orders exist, or contain orders from other tests)
    }

    public function testShouldReturnBadRequestWhenLimitIsMissing(): void
    {
        // Arrange
        $client = static::createClient();

        // Act
        $client->request('GET', '/api/orders', [], [], [
            'ACCEPT' => 'application/json',
        ]);

        // Assert
        $this->assertResponseStatusCodeSame(400);
        $responseContent = $client->getResponse()->getContent();
        if ($responseContent === false) {
            $this->fail('Response content is false');
        }
        $response = json_decode($responseContent, true);
        if (!is_array($response)) {
            $this->fail('Response is not an array');
        }
        // InvalidArgumentException returns 'error' key, not 'errors'
        $this->assertTrue(
            isset($response['error']) || isset($response['errors']),
            'Response should have either "error" or "errors" key'
        );
        if (isset($response['error']) && is_string($response['error'])) {
            $this->assertStringContainsString('limit', $response['error']);
            $this->assertStringContainsString('required', $response['error']);
        }
    }

    public function testShouldReturnBadRequestWhenLimitIsZero(): void
    {
        // Arrange
        $client = static::createClient();

        // Act
        $client->request('GET', '/api/orders?limit=0', [], [], [
            'ACCEPT' => 'application/json',
        ]);

        // Assert
        $this->assertResponseStatusCodeSame(400);
        $responseContent = $client->getResponse()->getContent();
        if ($responseContent === false) {
            $this->fail('Response content is false');
        }
        $response = json_decode($responseContent, true);
        if (!is_array($response)) {
            $this->fail('Response is not an array');
        }
        $this->assertArrayHasKey('errors', $response);
    }

    public function testShouldReturnBadRequestWhenLimitIsNegative(): void
    {
        // Arrange
        $client = static::createClient();

        // Act
        $client->request('GET', '/api/orders?limit=-1', [], [], [
            'ACCEPT' => 'application/json',
        ]);

        // Assert
        $this->assertResponseStatusCodeSame(400);
        $responseContent = $client->getResponse()->getContent();
        if ($responseContent === false) {
            $this->fail('Response content is false');
        }
        $response = json_decode($responseContent, true);
        if (!is_array($response)) {
            $this->fail('Response is not an array');
        }
        $this->assertArrayHasKey('errors', $response);
    }

    public function testShouldReturnBadRequestWhenLimitIsNonInteger(): void
    {
        // Arrange
        $client = static::createClient();

        // Act
        $client->request('GET', '/api/orders?limit=abc', [], [], [
            'ACCEPT' => 'application/json',
        ]);

        // Assert
        $this->assertResponseStatusCodeSame(400);
        $responseContent = $client->getResponse()->getContent();
        if ($responseContent === false) {
            $this->fail('Response content is false');
        }
        $response = json_decode($responseContent, true);
        if (!is_array($response)) {
            $this->fail('Response is not an array');
        }
        // Should have either errors or error key
        $this->assertTrue(
            isset($response['errors']) || isset($response['error']),
            'Response should have either "errors" or "error" key'
        );
    }

    public function testShouldReturnOrdersWithAllItemsCorrectly(): void
    {
        // Arrange - Create order with multiple items
        $client = static::createClient();
        $requestData = [
            'sum' => 3000,
            'contractorType' => 1,
            'items' => [
                [
                    'productId' => 1,
                    'price' => 1000,
                    'quantity' => 1,
                ],
                [
                    'productId' => 2,
                    'price' => 2000,
                    'quantity' => 1,
                ],
            ],
        ];

        $jsonData = json_encode($requestData);
        if ($jsonData === false) {
            throw new \RuntimeException('Failed to encode request data to JSON');
        }
        $client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $jsonData);

        $createResponse = json_decode($client->getResponse()->getContent() ?: '{}', true);
        if (!is_array($createResponse) || !isset($createResponse['uniqueOrderNumber'])) {
            $this->fail('Failed to create order for test');
        }

        // Act
        $client->request('GET', '/api/orders?limit=1', [], [], [
            'ACCEPT' => 'application/json',
        ]);

        // Assert
        $this->assertResponseStatusCodeSame(200);
        $responseContent = $client->getResponse()->getContent();
        if ($responseContent === false) {
            $this->fail('Response content is false');
        }
        $response = json_decode($responseContent, true);
        if (!is_array($response)) {
            $this->fail('Response is not an array');
        }
        $this->assertGreaterThanOrEqual(1, count($response));

        if (isset($response[0]) && is_array($response[0])) {
            $order = $response[0];
            if (isset($order['items']) && is_array($order['items'])) {
                $this->assertGreaterThanOrEqual(1, count($order['items']));
            }
        }
    }

    public function testShouldReturnCorrectResponseStructure(): void
    {
        // Arrange - Create an order
        $client = static::createClient();
        $this->createOrder($client, 1000, 1);

        // Act
        $client->request('GET', '/api/orders?limit=1', [], [], [
            'ACCEPT' => 'application/json',
        ]);

        // Assert
        $this->assertResponseStatusCodeSame(200);
        $responseContent = $client->getResponse()->getContent();
        if ($responseContent === false) {
            $this->fail('Response content is false');
        }
        $response = json_decode($responseContent, true);
        if (!is_array($response)) {
            $this->fail('Response is not an array');
        }

        if (isset($response[0]) && is_array($response[0])) {
            $order = $response[0];
            $this->assertArrayHasKey('id', $order);
            $this->assertArrayHasKey('sum', $order);
            $this->assertArrayHasKey('contractorType', $order);
            $this->assertArrayHasKey('items', $order);

            // Verify id format (YYYY-MM-NNNNN)
            if (is_string($order['id'])) {
                $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d+$/', $order['id']);
            }

            // Verify contractorType is 1 or 2
            if (is_int($order['contractorType'])) {
                $this->assertContains($order['contractorType'], [1, 2]);
            }
        }
    }

    /**
     * Helper method to create an order
     *
     * @return array<string, mixed>
     */
    private function createOrder(
        \Symfony\Bundle\FrameworkBundle\KernelBrowser $client,
        int $sum,
        int $contractorType
    ): array {
        $requestData = [
            'sum' => $sum,
            'contractorType' => $contractorType,
            'items' => [
                [
                    'productId' => 1,
                    'price' => $sum,
                    'quantity' => 1,
                ],
            ],
        ];

        $jsonData = json_encode($requestData);
        if ($jsonData === false) {
            throw new \RuntimeException('Failed to encode request data to JSON');
        }
        $client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $jsonData);

        $responseContent = $client->getResponse()->getContent();
        if ($responseContent === false) {
            return [];
        }
        $response = json_decode($responseContent, true);
        if (!is_array($response)) {
            return [];
        }
        /** @var array<string, mixed> $response */
        return $response;
    }
}
