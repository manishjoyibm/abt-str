<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Test\Unit\Model\Product\Subscription\Details\Config;

use Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Generic;
use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Processor as SubscriptionOptionProcessor;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Generic
 */
class GenericTest extends TestCase
{
    /**
     * @var Generic
     */
    private $genericConfig;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepositoryMock;

    /**
     * @var SubscriptionOptionRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionsRepositoryMock;

    /**
     * @var PlanRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $planRepositoryMock;

    /**
     * @var SubscriptionOptionProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriptionOptionProcessorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->productRepositoryMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->optionsRepositoryMock = $this->getMockForAbstractClass(SubscriptionOptionRepositoryInterface::class);
        $this->planRepositoryMock = $this->getMockForAbstractClass(PlanRepositoryInterface::class);
        $this->subscriptionOptionProcessorMock = $this->createPartialMock(
            SubscriptionOptionProcessor::class,
            ['getDetailedOptions', 'getProductPrices', 'getOptionPrices']
        );

        $this->genericConfig = $objectManager->getObject(
            Generic::class,
            [
                'productRepository' => $this->productRepositoryMock,
                'optionsRepository' => $this->optionsRepositoryMock,
                'planRepository' => $this->planRepositoryMock,
                'subscriptionOptionProcessor' => $this->subscriptionOptionProcessorMock,
            ]
        );
    }

    /**
     * Test getConfig method
     */
    public function testGetConfig()
    {
        $productId = 1;
        $productType = 'simple';
        $planId = 10;
        $optionId = 100;
        $optionDetails = [['label' => 'AAA', 'value' => 'BBB']];
        $productPrices = ['final_price' => ['amount' => 55.0]];
        $optionPrices = ['final_price' => ['amount' => 75.0]];
        $result = [
            'regularPrices' => [
                'productType' => $productType,
                'options' => [
                    0 => $productPrices,
                    $optionId => $optionPrices,
                ],
            ],
            'subscriptionDetails' => [$optionId => $optionDetails],
            'productType' => $productType,
            'productId' => $productId
        ];

        $subscriptionOptionMock = $this->getMockForAbstractClass(SubscriptionOptionInterface::class);
        $subscriptionOptionMock->expects($this->once())
            ->method('getPlanId')
            ->willReturn($planId);
        $subscriptionOptionMock->expects($this->atLeastOnce())
            ->method('getOptionId')
            ->willReturn($optionId);
        $this->optionsRepositoryMock->expects($this->atLeastOnce())
            ->method('getList')
            ->with($productId)
            ->willReturn([$subscriptionOptionMock]);

        $planDefinitionMock = $this->getMockForAbstractClass(PlanDefinitionInterface::class);
        $planMock = $this->getMockForAbstractClass(PlanInterface::class);
        $planMock->expects($this->once())
            ->method('getDefinition')
            ->willReturn($planDefinitionMock);
        $this->planRepositoryMock->expects($this->once())
            ->method('get')
            ->with($planId)
            ->willReturn($planMock);

        $this->subscriptionOptionProcessorMock->expects($this->once())
            ->method('getDetailedOptions')
            ->with($subscriptionOptionMock, $planDefinitionMock)
            ->willReturn($optionDetails);

        $productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn($productType);
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($productMock);

        $this->subscriptionOptionProcessorMock->expects($this->once())
            ->method('getProductPrices')
            ->with($productMock)
            ->willReturn($productPrices);
        $this->subscriptionOptionProcessorMock->expects($this->once())
            ->method('getOptionPrices')
            ->with($subscriptionOptionMock)
            ->willReturn($optionPrices);

        $this->assertEquals($result, $this->genericConfig->getConfig($productId, $productType));
    }

    /**
     * Test getConfig method if no options
     */
    public function testGetConfigNoOptions()
    {
        $productId = 1;
        $productType = 'simple';
        $productPrices = ['final_price' => ['amount' => 55.0]];
        $result = [
            'regularPrices' => [
                'productType' => $productType,
                'options' => [
                    0 => $productPrices,
                    ]
            ],
            'subscriptionDetails' => [],
            'productType' => $productType,
            'productId' => $productId
        ];

        $this->optionsRepositoryMock->expects($this->atLeastOnce())
            ->method('getList')
            ->with($productId)
            ->willReturn([]);

        $productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn($productType);
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($productMock);

        $this->subscriptionOptionProcessorMock->expects($this->once())
            ->method('getProductPrices')
            ->with($productMock)
            ->willReturn($productPrices);

        $this->assertEquals($result, $this->genericConfig->getConfig($productId, $productType));
    }

    /**
     * Test getConfig method if an error occurs
     */
    public function testGetConfigException()
    {
        $productId = 1;
        $productType = 'simple';
        $result = [];

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willThrowException(new NoSuchEntityException(__('No such entity!')));

        $this->assertEquals($result, $this->genericConfig->getConfig($productId, $productType));
    }
}
