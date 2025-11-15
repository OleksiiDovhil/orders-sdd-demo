<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Order\Query;

use App\Application\Order\DTO\CheckOrderCompletionResponseDTO;
use App\Application\Order\Query\CheckOrderCompletionHandler;
use App\Application\Order\Query\CheckOrderCompletionQuery;
use App\Application\Order\ValueObject\OrderCompletionMessage;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Entity\OrderItem;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Order\Service\PaymentStatusServiceInterface;
use App\Domain\Order\ValueObject\ContractorType;
use App\Domain\Order\ValueObject\OrderId;
use App\Domain\Order\ValueObject\OrderNumber;
use App\Domain\Order\ValueObject\UniqueOrderNumber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CheckOrderCompletionHandlerTest extends TestCase
{
    private OrderRepositoryInterface&MockObject $repository;
    private PaymentStatusServiceInterface&MockObject $paymentStatusService;
    private CheckOrderCompletionHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(OrderRepositoryInterface::class);
        $this->paymentStatusService = $this->createMock(PaymentStatusServiceInterface::class);
        $this->handler = new CheckOrderCompletionHandler(
            $this->repository,
            $this->paymentStatusService
        );
    }

    private function createOrder(ContractorType $contractorType, bool $isPaid = false): Order
    {
        return new Order(
            new OrderId(1),
            new OrderNumber(1),
            new UniqueOrderNumber('2025-11-1'),
            1000,
            $contractorType,
            new \DateTimeImmutable(),
            $isPaid,
            new OrderItem(1, 1000, 1)
        );
    }

    public function testShouldReturnPaidStatusForIndividualOrderWhenAlreadyPaid(): void
    {
        // Arrange
        $order = $this->createOrder(ContractorType::INDIVIDUAL, true);
        $query = new CheckOrderCompletionQuery('2025-11-1');

        $this->repository
            ->expects($this->once())
            ->method('findByUniqueOrderNumber')
            ->with($this->callback(function (UniqueOrderNumber $number) {
                return $number->getValue() === '2025-11-1';
            }))
            ->willReturn($order);


        // Payment service should not be called if already paid
        $this->paymentStatusService
            ->expects($this->never())
            ->method('checkPaymentStatus');

        // Act
        $result = $this->handler->handle($query);

        // Assert
        $this->assertInstanceOf(CheckOrderCompletionResponseDTO::class, $result);
        $this->assertTrue($result->isPaid);
        $this->assertSame(OrderCompletionMessage::PAID, $result->message);
    }

    public function testShouldReturnUnpaidStatusForIndividualOrder(): void
    {
        // Arrange
        $order = $this->createOrder(ContractorType::INDIVIDUAL);
        $query = new CheckOrderCompletionQuery('2025-11-1');

        $this->repository
            ->expects($this->once())
            ->method('findByUniqueOrderNumber')
            ->willReturn($order);


        $this->paymentStatusService
            ->expects($this->once())
            ->method('checkPaymentStatus')
            ->with($order)
            ->willReturn(false);

        // Should not save if service returns false
        $this->repository
            ->expects($this->never())
            ->method('save');

        // Act
        $result = $this->handler->handle($query);

        // Assert
        $this->assertInstanceOf(CheckOrderCompletionResponseDTO::class, $result);
        $this->assertFalse($result->isPaid);
        $this->assertSame(OrderCompletionMessage::PENDING, $result->message);
    }

    public function testShouldMarkAsPaidWhenServiceReturnsTrue(): void
    {
        // Arrange
        $order = $this->createOrder(ContractorType::INDIVIDUAL);
        $query = new CheckOrderCompletionQuery('2025-11-1');

        $this->repository
            ->expects($this->once())
            ->method('findByUniqueOrderNumber')
            ->willReturn($order);


        $this->paymentStatusService
            ->expects($this->once())
            ->method('checkPaymentStatus')
            ->with($order)
            ->willReturn(true);

        // Should save the order when service returns true (order will be marked as paid)
        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Order $savedOrder) use ($order) {
                return $savedOrder->getId()->equals($order->getId()) && $savedOrder->isPaid();
            }));

        // Act
        $result = $this->handler->handle($query);

        // Assert
        $this->assertInstanceOf(CheckOrderCompletionResponseDTO::class, $result);
        $this->assertTrue($result->isPaid);
        $this->assertSame(OrderCompletionMessage::PAID, $result->message);
    }

    public function testShouldReturnPaidStatusForLegalEntityOrderWhenAlreadyPaid(): void
    {
        // Arrange
        $order = $this->createOrder(ContractorType::LEGAL_ENTITY, true);
        $query = new CheckOrderCompletionQuery('2025-11-1');

        $this->repository
            ->expects($this->once())
            ->method('findByUniqueOrderNumber')
            ->willReturn($order);


        // Payment service should not be called if already paid
        $this->paymentStatusService
            ->expects($this->never())
            ->method('checkPaymentStatus');

        // Act
        $result = $this->handler->handle($query);

        // Assert
        $this->assertInstanceOf(CheckOrderCompletionResponseDTO::class, $result);
        $this->assertTrue($result->isPaid);
        $this->assertSame(OrderCompletionMessage::PAID, $result->message);
    }

    public function testShouldReturnUnpaidStatusForLegalEntityOrder(): void
    {
        // Arrange
        $order = $this->createOrder(ContractorType::LEGAL_ENTITY);
        $query = new CheckOrderCompletionQuery('2025-11-1');

        $this->repository
            ->expects($this->once())
            ->method('findByUniqueOrderNumber')
            ->willReturn($order);


        $this->paymentStatusService
            ->expects($this->once())
            ->method('checkPaymentStatus')
            ->with($order)
            ->willReturn(false);

        // Should not save if service returns false
        $this->repository
            ->expects($this->never())
            ->method('save');

        // Act
        $result = $this->handler->handle($query);

        // Assert
        $this->assertInstanceOf(CheckOrderCompletionResponseDTO::class, $result);
        $this->assertFalse($result->isPaid);
        $this->assertSame(OrderCompletionMessage::PENDING, $result->message);
    }

    public function testShouldMarkAsPaidForLegalEntityWhenServiceReturnsTrue(): void
    {
        // Arrange
        $order = $this->createOrder(ContractorType::LEGAL_ENTITY);
        $query = new CheckOrderCompletionQuery('2025-11-1');

        $this->repository
            ->expects($this->once())
            ->method('findByUniqueOrderNumber')
            ->willReturn($order);


        $this->paymentStatusService
            ->expects($this->once())
            ->method('checkPaymentStatus')
            ->with($order)
            ->willReturn(true);

        // Should save the order when service returns true (order will be marked as paid)
        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Order $savedOrder) use ($order) {
                return $savedOrder->getId()->equals($order->getId()) && $savedOrder->isPaid();
            }));

        // Act
        $result = $this->handler->handle($query);

        // Assert
        $this->assertInstanceOf(CheckOrderCompletionResponseDTO::class, $result);
        $this->assertTrue($result->isPaid);
        $this->assertSame(OrderCompletionMessage::PAID, $result->message);
    }

    public function testShouldThrowNotFoundExceptionWhenOrderNotFound(): void
    {
        // Arrange
        $query = new CheckOrderCompletionQuery('2025-11-999');

        $this->repository
            ->expects($this->once())
            ->method('findByUniqueOrderNumber')
            ->willReturn(null);

        // Assert
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Order not found');

        // Act
        $this->handler->handle($query);
    }
}

