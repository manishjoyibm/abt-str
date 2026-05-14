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
use Aheadworks\Sarp2\Api\SubscriptionPriceCalculationInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\CustomOptionCalculator;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\Trial;
use Aheadworks\Sarp2\Model\Sales\Total\PopulatorFactory;
use Aheadworks\Sarp2\Model\Sales\Total\ProviderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * Test for \Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\Trial
 */
class TrialTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Calculation trial price
     */
    const CALCULATION_PRICE = 12.00;

    /**
     * @var Trial
     */
    private $totalGroup;

    /**
     * @var SubscriptionOptionRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionRepositoryMock;

    /**
     * @var SubscriptionPriceCalculationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCalculationMock;

    /**
     * @var PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrencyMock;

    /**
     * @var PopulatorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $populatorFactoryMock;

    /**
     * @var ProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $providerMock;

    /**
     * @var CustomOptionCalculator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customOptionCalculatorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->optionRepositoryMock = $this->createMock(SubscriptionOptionRepositoryInterface::class);
        $this->priceCalculationMock = $this->createMock(SubscriptionPriceCalculationInterface::class);
        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);
        $this->populatorFactoryMock = $this->createMock(PopulatorFactory::class);
        $this->providerMock = $this->createMock(ProviderInterface::class);
        $this->customOptionCalculatorMock = $this->createMock(CustomOptionCalculator::class);

        $this->totalGroup = $objectManager->getObject(
            Trial::class,
            [
                'optionRepository' => $this->optionRepositoryMock,
                'priceCalculation' => $this->priceCalculationMock,
                'priceCurrency' => $this->priceCurrencyMock,
                'populatorFactory' => $this->populatorFactoryMock,
                'provider' => $this->providerMock,
                'customOptionCalculator' => $this->customOptionCalculatorMock,
            ]
        );
    }

    /**
     * @param float $trialPrice
     * @param bool $isTrialPeriodEnabled
     * @param bool $isAutoTrialPrice
     * @param bool $useBaseCurrency
     * @param float $expectedResult
     * @dataProvider getItemPriceDataProvider
     */
    public function testGetItemPrice(
        $trialPrice,
        $isTrialPeriodEnabled,
        $isAutoTrialPrice,
        $useBaseCurrency,
        $expectedResult
    ) {
        $subscriptionOptionId = 1;
        $productId = 2;
        $planId = 3;

        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->createMock(ItemInterface::class);
        $optionMock = $this->createMock(OptionInterface::class);
        $subscriptionOptionMock = $this->createMock(SubscriptionOptionInterface::class);
        $planMock = $this->createMock(PlanInterface::class);
        $definitionMock = $this->createMock(PlanDefinitionInterface::class);
        $productMock = $this->createMock(Product::class);

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
        $definitionMock->expects($this->once())
            ->method('getIsTrialPeriodEnabled')
            ->willReturn($isTrialPeriodEnabled);
        if ($isTrialPeriodEnabled) {
            $itemMock->expects($this->once())
                ->method('getProduct')
                ->willReturn($productMock);
            $subscriptionOptionMock->expects($this->once())
                ->method('getIsAutoTrialPrice')
                ->willReturn($isAutoTrialPrice);
            if ($isAutoTrialPrice) {
                $productMock->expects($this->once())
                    ->method('getEntityId')
                    ->willReturn($productId);
                $planMock->expects($this->once())
                    ->method('getPlanId')
                    ->willReturn($planId);
                $this->priceCalculationMock->expects($this->once())
                    ->method('getAutoTrialPrice')
                    ->with($productId, $planId)
                    ->willReturn(self::CALCULATION_PRICE);

                $this->customOptionCalculatorMock->expects($this->once())
                    ->method('applyOptionsPrice')
                    ->with($itemMock, self::CALCULATION_PRICE, $useBaseCurrency)
                    ->willReturn(self::CALCULATION_PRICE);
            } else {
                $subscriptionOptionMock->expects($this->once())
                    ->method('getTrialPrice')
                    ->willReturn($trialPrice);

                $this->customOptionCalculatorMock->expects($this->once())
                    ->method('applyOptionsPrice')
                    ->with($itemMock, $trialPrice, $useBaseCurrency)
                    ->willReturn($trialPrice);
            }
        }
        if (!$useBaseCurrency) {
            $this->priceCurrencyMock->expects($this->once())
                ->method('convert')
                ->willReturnArgument(0);
        }

        $this->customOptionCalculatorMock->expects($this->once())
            ->method('applyOptionsPrice')
            ->with($itemMock, $expectedResult, $useBaseCurrency)
            ->willReturn($expectedResult);

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
            [8.00, true, false, true, 8.00],
            [8.00, true, false, false, 8.00],
            [8.00, true, true, true, self::CALCULATION_PRICE],
            [8.00, true, true, false, self::CALCULATION_PRICE],
            [8.00, false, false, true, 0.00],
            [8.00, false, true, true, 0.00]
        ];
    }

    /**
     * @param float $trialPrice
     * @param bool $isTrialPeriodEnabled
     * @param bool $isAutoTrialPrice
     * @param bool $useBaseCurrency
     * @param bool $hasChildren
     * @param float $expectedResult
     * @dataProvider getItemPriceQuoteItemDataProvider
     */
    public function testGetItemPriceQuoteItem(
        $trialPrice,
        $isTrialPeriodEnabled,
        $isAutoTrialPrice,
        $useBaseCurrency,
        $hasChildren,
        $expectedResult
    ) {
        $subscriptionOptionId = 1;
        $productId = 2;
        $planId = 3;

        $optionMock = $this->createMock(OptionInterface::class);
        $optionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($subscriptionOptionId);

        $subscriptionOptionMock = $this->createMock(SubscriptionOptionInterface::class);
        $planMock = $this->createMock(PlanInterface::class);
        $definitionMock = $this->createMock(PlanDefinitionInterface::class);
        $productMock = $this->createMock(Product::class);

        $itemMock = $this->getQuoteItemMock($optionMock, $hasChildren, $productMock);

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
        $definitionMock->expects($this->once())
            ->method('getIsTrialPeriodEnabled')
            ->willReturn($isTrialPeriodEnabled);
        if ($isTrialPeriodEnabled) {
            $subscriptionOptionMock->expects($this->once())
                ->method('getIsAutoTrialPrice')
                ->willReturn($isAutoTrialPrice);
            if ($isAutoTrialPrice) {
                $productMock->expects($this->once())
                    ->method('getEntityId')
                    ->willReturn($productId);
                $planMock->expects($this->once())
                    ->method('getPlanId')
                    ->willReturn($planId);
                $this->priceCalculationMock->expects($this->once())
                    ->method('getAutoTrialPrice')
                    ->with($productId, $planId)
                    ->willReturn(self::CALCULATION_PRICE);

                $this->customOptionCalculatorMock->expects($this->once())
                    ->method('applyOptionsPrice')
                    ->with($itemMock, self::CALCULATION_PRICE, $useBaseCurrency)
                    ->willReturn(self::CALCULATION_PRICE);
            } else {
                $subscriptionOptionMock->expects($this->once())
                    ->method('getTrialPrice')
                    ->willReturn($trialPrice);

                $this->customOptionCalculatorMock->expects($this->once())
                    ->method('applyOptionsPrice')
                    ->with($itemMock, $trialPrice, $useBaseCurrency)
                    ->willReturn($trialPrice);
            }
        }
        if (!$useBaseCurrency) {
            $this->priceCurrencyMock->expects($this->once())
                ->method('convert')
                ->willReturnArgument(0);
        }

        $this->assertEquals($expectedResult, $this->totalGroup->getItemPrice($itemMock, $useBaseCurrency));
    }

    /**
     * Get quote item mock
     *
     * @param OptionInterface|\PHPUnit_Framework_MockObject_MockObject $optionMock
     * @param bool $hasChildren
     * @param Product|\PHPUnit_Framework_MockObject_MockObject $productMock
     * @return Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getQuoteItemMock($optionMock, $hasChildren, $productMock)
    {
        if ($hasChildren) {
            $itemMock = $this->getConfigurableItemMock($optionMock, $productMock);
        } else {
            $itemMock = $this->getSimpleItemMock($optionMock, $productMock);
        }

        return $itemMock;
    }

    /**
     * Get configurable item mock
     *
     * @param OptionInterface|\PHPUnit_Framework_MockObject_MockObject $optionMock
     * @param Product|\PHPUnit_Framework_MockObject_MockObject $childProductMock
     * @return Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getConfigurableItemMock($optionMock, $childProductMock)
    {
        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->createPartialMock(
            Item::class,
            ['getOptionByCode', 'getHasChildren', 'getChildren', 'getProduct']
        );
        $childItem = $this->createPartialMock(
            Item::class,
            ['getProduct']
        );
        $itemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('aw_sarp2_subscription_type')
            ->willReturn($optionMock);
        $itemMock->expects($this->any())
            ->method('getHasChildren')
            ->willReturn(true);
        $itemMock->expects($this->any())
            ->method('getChildren')
            ->willReturn([$childItem]);
        $childItem->expects($this->any())
            ->method('getProduct')
            ->willReturn($childProductMock);

        return $itemMock;
    }

    /**
     * Get simple item mock
     *
     * @param OptionInterface|\PHPUnit_Framework_MockObject_MockObject $optionMock
     * @param Product|\PHPUnit_Framework_MockObject_MockObject $productMock
     * @return Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getSimpleItemMock($optionMock, $productMock)
    {
        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->createPartialMock(
            Item::class,
            ['getOptionByCode', 'getHasChildren', 'getProduct']
        );
        $itemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('aw_sarp2_subscription_type')
            ->willReturn($optionMock);
        $itemMock->expects($this->any())
            ->method('getHasChildren')
            ->willReturn(false);
        $itemMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($productMock);

        return $itemMock;
    }

    /**
     * @return array
     */
    public function getItemPriceQuoteItemDataProvider()
    {
        return [
            [8.00, true, false, true, true, 8.00],
            [8.00, true, false, false, true, 8.00],
            [8.00, true, true, true, true, self::CALCULATION_PRICE],
            [8.00, true, true, false, true, self::CALCULATION_PRICE],
            [8.00, false, false, true, true, 0.00],
            [8.00, false, true, true, true, 0.00],
            [8.00, true, false, true, false, 8.00],
            [8.00, true, false, false, false, 8.00],
            [8.00, true, true, true, false, self::CALCULATION_PRICE],
            [8.00, true, true, false, false, self::CALCULATION_PRICE],
            [8.00, false, false, true, false, 0.00],
            [8.00, false, true, true, false, 0.00]
        ];
    }
}
