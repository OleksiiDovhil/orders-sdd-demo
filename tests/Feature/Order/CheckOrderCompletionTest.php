<?php

declare(strict_types=1);

namespace App\Tests\Feature\Order;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CheckOrderCompletionTest extends WebTestCase
{
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

        $client->request('POST', '/api/orders', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($requestData));

        $this->assertResponseStatusCodeSame(201);
        $response = json_decode($client->getResponse()->getContent(), true);
        $uniqueOrderNumber = $response['uniqueOrderNumber'];

        // Update is_paid for individual orders
        if ($contractorType === 1 && $isPaid) {
            $pdo = static::getContainer()->get(\PDO::class);
            $stmt = $pdo->prepare('UPDATE orders SET is_paid = :is_paid WHERE unique_order_number = :unique_order_number');
            $stmt->execute([
                ':is_paid' => $isPaid ? 1 : 0,
                ':unique_order_number' => $uniqueOrderNumber,
            ]);
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
        $response = json_decode($client->getResponse()->getContent(), true);
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
        $response = json_decode($client->getResponse()->getContent(), true);
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
        $response = json_decode($client->getResponse()->getContent(), true);
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
        if ($responseContent !== null && $responseContent !== '') {
            $response = json_decode($responseContent, true);
            if ($response !== null) {
                $this->assertIsArray($response);
                if (isset($response['error'])) {
                    $this->assertStringContainsString('not found', strtolower($response['error']));
                }
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
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('uniqueOrderNumber', $response['errors']);
    }

    public function testShouldReturn400WhenUniqueOrderNumberFormatIsMissingYear(): void
    {
        // Arrange
        $client = static::createClient();

        // Act
        $client->request('GET', '/api/orders/11-12345/complete');

        // Assert
        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('uniqueOrderNumber', $response['errors']);
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
        $response = json_decode($client->getResponse()->getContent(), true);
        
        // Verify exact structure
        $this->assertCount(2, $response);
        $this->assertArrayHasKey('isPaid', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertIsBool($response['isPaid']);
        $this->assertIsString($response['message']);
        $this->assertNotEmpty($response['message']);
    }
}

