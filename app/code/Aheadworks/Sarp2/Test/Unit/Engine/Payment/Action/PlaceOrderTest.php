<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Action;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Exception\PaymentException;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Action\PlaceOrder;
use Aheadworks\Sarp2\Engine\Payment\Action\Exception\HandlerInterface;
use Aheadworks\Sarp2\Engine\Payment\Action\Exception\HandlerResolver;
use Aheadworks\Sarp2\Engine\Profile\PaymentInfoInterface;
use Aheadworks\Sarp2\Model\Profile\Merged\Set\DataResolver;
use Aheadworks\Sarp2\Model\Sales\Order\InventoryManagement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Action\PlaceOrder
 */
class PlaceOrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PlaceOrder
     */
    private $action;

    /**
     * @var OrderManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderManagementMock;

    /**
     * @var InventoryManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inventoryManagementMock;

    /**
     * @var DataResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $setDataResolverMock;

    /**
     * @var HandlerResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $exceptionHandlerResolverMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->orderManagementMock = $this->createMock(OrderManagementInterface::class);
        $this->inventoryManagementMock = $this->createMock(InventoryManagement::class);
        $this->setDataResolverMock = $this->createMock(DataResolver::class);
        $this->exceptionHandlerResolverMock = $this->createMock(HandlerResolver::class);
        $this->action = $objectManager->getObject(
            PlaceOrder::class,
            [
                'orderManagement' => $this->orderManagementMock,
                'inventoryManagement' => $this->inventoryManagementMock,
                'setDataResolver' => $this->setDataResolverMock,
                'exceptionHandlerResolver' => $this->exceptionHandlerResolverMock
            ]
        );
    }

    public function testPlace()
    {
        /** @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject $orderMock */
        $orderMock = $this->createMock(OrderInterface::class);
        /** @var PaymentInfoInterface|\PHPUnit_Framework_MockObject_MockObject $paymentInfoMock */
        $paymentInfoMock = $this->createMock(PaymentInfoInterface::class);
        $profileMock = $this->createMock(ProfileInterface::class);

        $paymentInfoMock->expects($this->once())
            ->method('getProfile')
            ->willReturn($profileMock);
        $this->inventoryManagementMock->expects($this->once())
            ->method('subtract')
            ->with($profileMock);
        $this->orderManagementMock->expects($this->once())
            ->method('place')
            ->with($orderMock);

        $this->assertSame($orderMock, $this->action->place($orderMock, [$paymentInfoMock]));
    }

    /**
     * @expectedException \Aheadworks\Sarp2\Engine\Exception\PaymentException
     * @expectedExceptionMessage Payment error.
     */
    public function testPlaceHandledException()
    {
        $paymentMethod = 'braintree';
        $exception = new \Exception('Gateway error.');
        $processedException = new PaymentException(__('Payment error.'));

        /** @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject $orderMock */
        $orderMock = $this->createMock(OrderInterface::class);
        /** @var PaymentInfoInterface|\PHPUnit_Framework_MockObject_MockObject $paymentInfoMock */
        $paymentInfoMock = $this->createMock(PaymentInfoInterface::class);
        $profileMock = $this->createMock(ProfileInterface::class);
        $exceptionHandlerMock = $this->createMock(HandlerInterface::class);

        $paymentInfoMock->expects($this->once())
            ->method('getProfile')
            ->willReturn($profileMock);
        $this->inventoryManagementMock->expects($this->once())
            ->method('subtract')
            ->with($profileMock);
        $this->orderManagementMock->expects($this->once())
            ->method('place')
            ->with($orderMock)
            ->willThrowException($exception);
        $this->inventoryManagementMock->expects($this->once())
            ->method('revert')
            ->with($profileMock);
        $this->setDataResolverMock->expects($this->once())
            ->method('getPaymentMethod')
            ->with([$paymentInfoMock])
            ->willReturn($paymentMethod);
        $this->exceptionHandlerResolverMock->expects($this->once())
            ->method('getHandler')
            ->with($exception, $paymentMethod)
            ->willReturn($exceptionHandlerMock);
        $exceptionHandlerMock->expects($this->once())
            ->method('handle')
            ->with($exception)
            ->willReturn($processedException);

        $this->action->place($orderMock, [$paymentInfoMock]);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Persistence error.
     */
    public function testPlaceUnhandledException()
    {
        $paymentMethod = 'braintree';
        $exception = new \Exception('Persistence error.');

        /** @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject $orderMock */
        $orderMock = $this->createMock(OrderInterface::class);
        /** @var PaymentInfoInterface|\PHPUnit_Framework_MockObject_MockObject $paymentInfoMock */
        $paymentInfoMock = $this->createMock(PaymentInfoInterface::class);
        $profileMock = $this->createMock(ProfileInterface::class);

        $paymentInfoMock->expects($this->once())
            ->method('getProfile')
            ->willReturn($profileMock);
        $this->inventoryManagementMock->expects($this->once())
            ->method('subtract')
            ->with($profileMock);
        $this->orderManagementMock->expects($this->once())
            ->method('place')
            ->with($orderMock)
            ->willThrowException($exception);
        $this->inventoryManagementMock->expects($this->once())
            ->method('revert')
            ->with($profileMock);
        $this->setDataResolverMock->expects($this->once())
            ->method('getPaymentMethod')
            ->with([$paymentInfoMock])
            ->willReturn($paymentMethod);
        $this->exceptionHandlerResolverMock->expects($this->once())
            ->method('getHandler')
            ->with($exception, $paymentMethod)
            ->willReturn(null);

        $this->action->place($orderMock, [$paymentInfoMock]);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Subtract error.
     */
    public function testPlaceSubtractException()
    {
        $exception = new \Exception('Subtract error.');

        /** @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject $orderMock */
        $orderMock = $this->createMock(OrderInterface::class);
        /** @var PaymentInfoInterface|\PHPUnit_Framework_MockObject_MockObject $successInfoMock */
        $successInfoMock = $this->createMock(PaymentInfoInterface::class);
        $successProfileMock = $this->createMock(ProfileInterface::class);
        /** @var PaymentInfoInterface|\PHPUnit_Framework_MockObject_MockObject $failedInfoMock */
        $failedInfoMock = $this->createMock(PaymentInfoInterface::class);
        $failedProfileMock = $this->createMock(ProfileInterface::class);

        $successInfoMock->expects($this->once())
            ->method('getProfile')
            ->willReturn($successProfileMock);
        $failedInfoMock->expects($this->once())
            ->method('getProfile')
            ->willReturn($failedProfileMock);
        $this->inventoryManagementMock->expects($this->at(0))
            ->method('subtract')
            ->with($successProfileMock);
        $this->inventoryManagementMock->expects($this->at(1))
            ->method('subtract')
            ->with($failedProfileMock)
            ->willThrowException($exception);
        $this->inventoryManagementMock->expects($this->once())
            ->method('revert')
            ->with($successProfileMock);

        $this->action->place($orderMock, [$successInfoMock, $failedInfoMock]);
    }
}
