<?php

declare(strict_types=1);

namespace App\Tests\Feature\Order;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CheckOrderCompletionTest extends WebTestCase
{
    /**
     * @param array<string, mixed> $options
     */
    protected static function createKernel(array $options = []): \Symfony\Component\HttpKernel\KernelInterface
    {
        return new \App\Kernel('test', true);
    }

    private function createOrder(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, int $contractorType, bool $isPaid = false): string
    {
        $requestData = [
            'sum' => 1000,
            'contractorType' => $contractorType,
            'items' => [
                [
                    'productId' => 1,
                    'price' => 1000,
                    'quantity' => 1,
                ],
            ],
        ];

        $jsonData = json_encode($requestData);
        if ($jsonData === false) {
            $this->fail('Failed to encode request data to JSON');
        }
        $client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $jsonData);

        $this->assertResponseStatusCodeSame(201);
        $responseContent = $client->getResponse()->getContent();
        if ($responseContent === false) {
            $this->fail('Response content is false');
        }
        $response = json_decode($responseContent, true);
        if (!is_array($response) || !isset($response['uniqueOrderNumber']) || !is_string($response['uniqueOrderNumber'])) {
            $this->fail('Invalid response format');
        }
        $uniqueOrderNumber = $response['uniqueOrderNumber'];

        // Update is_paid for individual orders
        if ($contractorType === 1 && $isPaid === true) {
            $pdo = static::getContainer()->get(\PDO::class);
            if ($pdo instanceof \PDO) {
                $stmt = $pdo->prepare('UPDATE orders SET is_paid = :is_paid WHERE unique_order_number = :unique_order_number');
                if ($stmt !== false) {
                    $stmt->execute([
                        ':is_paid' => 1,
                        ':unique_order_number' => $uniqueOrderNumber,
                    ]);
                }
            }
        }

        return $uniqueOrderNumber;
    }

    public function testShouldReturn200WithIsPaidTrueForPaidIndividualOrder(): void
    {
        // Arrange
        $client = static::createClient();
        $uniqueOrderNumber = $this->createOrder($client, 1, true);

        // Act
        $client->request('GET', "/api/orders/{$uniqueOrderNumber}/complete");

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
        $this->assertArrayHasKey('isPaid', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertTrue($response['isPaid']);
        $this->assertEquals('Order has been paid successfully. Thank you for your purchase!', $response['message']);
    }

    public function testShouldReturn200WithIsPaidFalseForUnpaidIndividualOrder(): void
    {
        // Arrange
        $client = static::createClient();
        $uniqueOrderNumber = $this->createOrder($client, 1, false);

        // Act
        $client->request('GET', "/api/orders/{$uniqueOrderNumber}/complete");

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
        $this->assertArrayHasKey('isPaid', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertFalse($response['isPaid']);
        $this->assertEquals('Order payment is pending. Please complete the payment to proceed.', $response['message']);
    }

    public function testShouldReturn200ForLegalEntityOrder(): void
    {
        // Arrange
        $client = static::createClient();
        $uniqueOrderNumber = $this->createOrder($client, 2);

        // Act
        $client->request('GET', "/api/orders/{$uniqueOrderNumber}/complete");

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
        $this->assertArrayHasKey('isPaid', $response);
        $this->assertArrayHasKey('message', $response);
        // For legal entities, the mock returns random values, so we just verify structure
        $this->assertIsBool($response['isPaid']);
        $this->assertIsString($response['message']);
    }

    public function testShouldReturn404WhenOrderNotFound(): void
    {
        // Arrange
        $client = static::createClient();

        // Act
        $client->request('GET', '/api/orders/2025-11-99999/complete');

        // Assert
        $this->assertResponseStatusCodeSame(404);
        $responseContent = $client->getResponse()->getContent();
        if (is_string($responseContent) && $responseContent !== '') {
            $response = json_decode($responseContent, true);
            if (is_array($response) && isset($response['error']) && is_string($response['error'])) {
                $this->assertStringContainsString('not found', strtolower($response['error']));
            }
        }
    }

    public function testShouldReturn400WhenUniqueOrderNumberFormatIsInvalid(): void
    {
        // Arrange
        $client = static::createClient();

        // Act
        $client->request('GET', '/api/orders/invalid-format/complete');

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
        if (is_array($response['errors'])) {
            $this->assertArrayHasKey('uniqueOrderNumber', $response['errors']);
        }
    }

    public function testShouldReturn400WhenUniqueOrderNumberFormatIsMissingYear(): void
    {
        // Arrange
        $client = static::createClient();

        // Act
        $client->request('GET', '/api/orders/11-12345/complete');

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
        if (is_array($response['errors'])) {
            $this->assertArrayHasKey('uniqueOrderNumber', $response['errors']);
        }
    }

    public function testShouldVerifyResponseStructure(): void
    {
        // Arrange
        $client = static::createClient();
        $uniqueOrderNumber = $this->createOrder($client, 1, false);

        // Act
        $client->request('GET', "/api/orders/{$uniqueOrderNumber}/complete");

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
        
        // Verify exact structure
        $this->assertCount(2, $response);
        $this->assertArrayHasKey('isPaid', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertIsBool($response['isPaid']);
        $this->assertIsString($response['message']);
        $this->assertNotEmpty($response['message']);
    }
}

