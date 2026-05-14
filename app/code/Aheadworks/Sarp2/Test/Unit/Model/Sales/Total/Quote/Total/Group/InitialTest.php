<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Test\Unit\Model\Sales\Total\Quote\Total\Group;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\Initial;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;

/**
 * Test for \Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\Initial
 */
class InitialTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Initial
     */
    private $totalGroup;

    /**
     * @var SubscriptionOptionRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionRepositoryMock;

    /**
     * @var PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrencyMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->optionRepositoryMock = $this->createMock(SubscriptionOptionRepositoryInterface::class);
        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);
        $this->totalGroup = $objectManager->getObject(
            Initial::class,
            [
                'optionRepository' => $this->optionRepositoryMock,
                'priceCurrency' => $this->priceCurrencyMock
            ]
        );
    }

    /**
     * @param float $initialFee
     * @param bool $isInitialFeeEnabled
     * @param bool $useBaseCurrency
     * @param float $expectedResult
     * @dataProvider getItemPriceDataProvider
     */
    public function testGetItemPrice(
        $initialFee,
        $isInitialFeeEnabled,
        $useBaseCurrency,
        $expectedResult
    ) {
        $subscriptionOptionId = 1;

        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->createMock(ItemInterface::class);
        $optionMock = $this->createMock(OptionInterface::class);
        $subscriptionOptionMock = $this->createMock(SubscriptionOptionInterface::class);
        $planMock = $this->createMock(PlanInterface::class);
        $definitionMock = $this->createMock(PlanDefinitionInterface::class);

        $itemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('aw_sarp2_subscription_type')
            ->willReturn($optionMock);
        $optionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($subscriptionOptionId);
        $this->optionRepositoryMock->expects($this->once())
            ->method('get')
            ->with($subscriptionOptionId)
            ->willReturn($subscriptionOptionMock);
        $subscriptionOptionMock->expects($this->once())
            ->method('getPlan')
            ->willReturn($planMock);
        $planMock->expects($this->once())
            ->method('getDefinition')
            ->willReturn($definitionMock);
        $subscriptionOptionMock->expects($this->once())
            ->method('getInitialFee')
            ->willReturn($initialFee);
        if ($initialFee > 0) {
            $definitionMock->expects($this->once())
                ->method('getIsInitialFeeEnabled')
                ->willReturn($isInitialFeeEnabled);
        }
        if (!$useBaseCurrency) {
            $this->priceCurrencyMock->expects($this->once())
                ->method('convert')
                ->willReturnArgument(0);
        }

        $this->assertEquals($expectedResult, $this->totalGroup->getItemPrice($itemMock, $useBaseCurrency));
    }

    public function testGetItemPriceNonSubscription()
    {
        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->createMock(ItemInterface::class);
        $itemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('aw_sarp2_subscription_type')
            ->willReturn(null);
        $this->assertEquals(0, $this->totalGroup->getItemPrice($itemMock, true));
    }

    /**
     * @return array
     */
    public function getItemPriceDataProvider()
    {
        return [
            [2.00, true, true, 2.00],
            [2.00, true, false, 2.00],
            [0.00, true, true, 0.00],
            [2.00, false, true, 0.00],
        ];
    }
}
