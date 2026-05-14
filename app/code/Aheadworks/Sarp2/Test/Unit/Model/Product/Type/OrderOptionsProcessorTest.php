<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Test\Unit\Model\Product\Type;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Model\Product\Type\OrderOptionsProcessor;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;

/**
 * Test for \Aheadworks\Sarp2\Model\Product\Type\OrderOptionsProcessor
 */
class OrderOptionsProcessorTest extends TestCase
{
    /**
     * @var OrderOptionsProcessor
     */
    private $orderOptionsProcessor;

    /**
     * @var SubscriptionOptionRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriptionOptionRepositoryMock;

    /**
     * @var PlanRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $planRepositoryMock;

    /**
     * @var DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectProcessorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->subscriptionOptionRepositoryMock = $this->getMockForAbstractClass(
            SubscriptionOptionRepositoryInterface::class
        );
        $this->planRepositoryMock = $this->getMockForAbstractClass(
            PlanRepositoryInterface::class
        );
        $this->dataObjectProcessorMock = $this->createPartialMock(
            DataObjectProcessor::class,
            ['buildOutputDataArray']
        );

        $this->orderOptionsProcessor = $objectManager->getObject(
            OrderOptionsProcessor::class,
            [
                'subscriptionOptionRepository' => $this->subscriptionOptionRepositoryMock,
                'planRepository' => $this->planRepositoryMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock,
            ]
        );
    }

    /**
     * Test process method
     */
    public function testProcess()
    {
        $planId = 1;
        $subscriptionOptionId = 10;
        $planData = [
            'plan_id' => $planId
        ];
        $optionData = [
            'optionId_id' => $subscriptionOptionId
        ];
        $options = [];
        $resultOptions = [
            'aw_sarp2_subscription_plan' => $planData,
            'aw_sarp2_subscription_option' => $optionData
        ];

        $productOptionMock = $this->getMockForAbstractClass(OptionInterface::class);
        $productOptionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($subscriptionOptionId);

        $productMock = $this->createPartialMock(Product::class, ['getCustomOption']);
        $productMock->expects($this->once())
            ->method('getCustomOption')
            ->with('aw_sarp2_subscription_type')
            ->willReturn($productOptionMock);

        $subscriptionOptionMock = $this->getMockForAbstractClass(SubscriptionOptionInterface::class);
        $subscriptionOptionMock->expects($this->once())
            ->method('getPlanId')
            ->willReturn($planId);
        $this->subscriptionOptionRepositoryMock->expects($this->once())
            ->method('get')
            ->with($subscriptionOptionId)
            ->willReturn($subscriptionOptionMock);

        $planMock = $this->getMockForAbstractClass(PlanInterface::class);
        $this->planRepositoryMock->expects($this->once())
            ->method('get')
            ->with($planId)
            ->willReturn($planMock);

        $this->dataObjectProcessorMock->expects($this->exactly(2))
            ->method('buildOutputDataArray')
            ->withConsecutive(
                [$planMock, PlanInterface::class],
                [$subscriptionOptionMock, SubscriptionOptionInterface::class ]
            )->willReturnOnConsecutiveCalls($planData, $optionData);

        $this->assertEquals($resultOptions, $this->orderOptionsProcessor->process($productMock, $options));
    }

    /**
     * Test process method if no subscription option
     */
    public function testProcessNoOption()
    {
        $options = [];
        $resultOptions = [];

        $productMock = $this->createPartialMock(Product::class, ['getCustomOption']);
        $productMock->expects($this->once())
            ->method('getCustomOption')
            ->with('aw_sarp2_subscription_type')
            ->willReturn(null);

        $this->assertEquals($resultOptions, $this->orderOptionsProcessor->process($productMock, $options));
    }

    /**
     * Test process method if an error occurs
     */
    public function testProcessException()
    {
        $subscriptionOptionId = 10;
        $options = [];
        $resultOptions = [];

        $productOptionMock = $this->getMockForAbstractClass(OptionInterface::class);
        $productOptionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($subscriptionOptionId);

        $productMock = $this->createPartialMock(Product::class, ['getCustomOption']);
        $productMock->expects($this->once())
            ->method('getCustomOption')
            ->with('aw_sarp2_subscription_type')
            ->willReturn($productOptionMock);

        $this->subscriptionOptionRepositoryMock->expects($this->once())
            ->method('get')
            ->with($subscriptionOptionId)
            ->willThrowException(new NoSuchEntityException(__('No such entity!')));

        $this->assertEquals($resultOptions, $this->orderOptionsProcessor->process($productMock, $options));
    }
}
