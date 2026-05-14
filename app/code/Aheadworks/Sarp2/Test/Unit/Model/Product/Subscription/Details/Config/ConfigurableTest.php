<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Test\Unit\Model\Product\Subscription\Details\Config;

use Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Configurable;
use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Processor as SubscriptionOptionProcessor;
use Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Configurable\ChildProcessor;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Configurable
 */
class ConfigurableTest extends TestCase
{
    /**
     * @var Configurable
     */
    private $configurableConfig;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepositoryMock;

    /**
     * @var PlanRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $planRepositoryMock;

    /**
     * @var SubscriptionOptionProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriptionOptionProcessorMock;

    /**
     * @var ChildProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $childProcessorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->productRepositoryMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->planRepositoryMock = $this->getMockForAbstractClass(PlanRepositoryInterface::class);
        $this->subscriptionOptionProcessorMock = $this->createPartialMock(
            SubscriptionOptionProcessor::class,
            ['getDetailedOptions', 'getProductPrices', 'getOptionPrices']
        );
        $this->childProcessorMock = $this->createPartialMock(
            ChildProcessor::class,
            ['getAllowedList', 'getSubscriptionOptions']
        );

        $this->configurableConfig = $objectManager->getObject(
            Configurable::class,
            [
                'productRepository' => $this->productRepositoryMock,
                'planRepository' => $this->planRepositoryMock,
                'subscriptionOptionProcessor' => $this->subscriptionOptionProcessorMock,
                'childProcessor' => $this->childProcessorMock,
            ]
        );
    }

    /**
     * Test getConfig method
     */
    public function testGetConfig()
    {
        $productId = 1;
        $childProductId = 2;
        $productType = 'configurable';
        $planId = 10;
        $optionId = 100;
        $optionDetails = [['label' => 'AAA', 'value' => 'BBB']];
        $productPrices = ['final_price' => ['amount' => 55.0]];
        $optionPrices = ['final_price' => ['amount' => 75.0]];
        $result = [
            'regularPrices' => [
                'productType' => $productType,
                'options' => [
                    0 => [$childProductId => $productPrices],
                    $optionId => [$childProductId => $optionPrices],
                ],
            ],
            'subscriptionDetails' => [$optionId => [$childProductId => $optionDetails]],
            'productType' => $productType,
            'productId' => $productId
        ];

        $productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn($productType);
        $this->productRepositoryMock->expects($this->atLeastOnce())
            ->method('getById')
            ->willReturn($productMock);
        $childProductMock = $this->getMockForAbstractClass(ProductInterface::class);
        $childProductMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($childProductId);
        $this->childProcessorMock->expects($this->atLeastOnce())
            ->method('getAllowedList')
            ->with($productMock)
            ->willReturn([$childProductMock]);

        $subscriptionOptionMock = $this->getMockForAbstractClass(SubscriptionOptionInterface::class);
        $subscriptionOptionMock->expects($this->once())
            ->method('getPlanId')
            ->willReturn($planId);
        $subscriptionOptionMock->expects($this->atLeastOnce())
            ->method('getOptionId')
            ->willReturn($optionId);
        $this->childProcessorMock->expects($this->atLeastOnce())
            ->method('getSubscriptionOptions')
            ->with($childProductMock, $productId)
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

        $this->subscriptionOptionProcessorMock->expects($this->once())
            ->method('getProductPrices')
            ->with($productMock)
            ->willReturn($productPrices);
        $this->subscriptionOptionProcessorMock->expects($this->once())
            ->method('getOptionPrices')
            ->with($subscriptionOptionMock)
            ->willReturn($optionPrices);

        $this->assertEquals($result, $this->configurableConfig->getConfig($productId, $productType));
    }

    /**
     * Test getConfig method if no options
     */
    public function testGetConfigNoOptions()
    {
        $productId = 1;
        $childProductId = 2;
        $productType = 'configurable';
        $productPrices = ['final_price' => ['amount' => 55.0]];
        $result = [
            'regularPrices' => [
                'productType' => $productType,
                'options' => [
                    0 => [$childProductId => $productPrices],
                ]
            ],
            'subscriptionDetails' => [],
            'productType' => $productType,
            'productId' => $productId
        ];

        $productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn($productType);
        $this->productRepositoryMock->expects($this->atLeastOnce())
            ->method('getById')
            ->willReturn($productMock);
        $childProductMock = $this->getMockForAbstractClass(ProductInterface::class);
        $childProductMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($childProductId);
        $this->childProcessorMock->expects($this->atLeastOnce())
            ->method('getAllowedList')
            ->with($productMock)
            ->willReturn([$childProductMock]);

        $this->childProcessorMock->expects($this->atLeastOnce())
            ->method('getSubscriptionOptions')
            ->with($childProductMock, $productId)
            ->willReturn([]);

        $this->subscriptionOptionProcessorMock->expects($this->once())
            ->method('getProductPrices')
            ->with($productMock)
            ->willReturn($productPrices);

        $this->assertEquals($result, $this->configurableConfig->getConfig($productId, $productType));
    }

    /**
     * Test getConfig method if an error occurs
     */
    public function testGetConfigException()
    {
        $productId = 1;
        $productType = 'configurable';
        $result = [];

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willThrowException(new NoSuchEntityException(__('No such entity!')));

        $this->assertEquals($result, $this->configurableConfig->getConfig($productId, $productType));
    }
}
