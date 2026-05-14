<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Test\Unit\Model\Product\Subscription\Configuration;

use Aheadworks\Sarp2\Model\Product\Subscription\Configuration\OptionResolver;
use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Test for \Aheadworks\Sarp2\Model\Product\Subscription\Configuration\OptionResolver
 */
class OptionResolverTest extends TestCase
{
    /**
     * @var OptionResolver
     */
    private $optionResolver;

    /**
     * @var SubscriptionOptionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriptionOptionFactoryMock;

    /**
     * @var TaxConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxConfigMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->subscriptionOptionFactoryMock = $this->createPartialMock(
            SubscriptionOptionInterfaceFactory::class,
            ['create']
        );
        $this->taxConfigMock = $this->createMock(TaxConfig::class);

        $this->optionResolver = $objectManager->getObject(
            OptionResolver::class,
            [
                'subscriptionOptionFactory' =>  $this->subscriptionOptionFactoryMock,
                'taxConfig' => $this->taxConfigMock,
            ]
        );
    }

    /**
     * Test getSubscriptionOption method
     *
     * @param bool $priceIncludesTax
     * @dataProvider getSubscriptionOptionDataProvider
     */
    public function testGetSubscriptionOption($priceIncludesTax)
    {
        $planId = 1;
        $productId = 2;
        $initialFee = 10;
        $trialPrice = 20;
        $regularPrice = 100;

        $planMock = $this->getMockForAbstractClass(PlanInterface::class);
        $planMock->expects($this->once())
            ->method('getPlanId')
            ->willReturn($planId);

        $productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);

        $quoteItemMock = $this->getQuoteItemMock(
            $productMock,
            $initialFee,
            $trialPrice,
            $regularPrice,
            $priceIncludesTax
        );

        $this->taxConfigMock->expects($this->once())
            ->method('priceIncludesTax')
            ->willReturn($priceIncludesTax);

        $subscriptionOptionMock = $this->getSubscriptionOptionMock(
            $planMock,
            $planId,
            $productId,
            $initialFee,
            $trialPrice,
            $regularPrice
        );

        $this->subscriptionOptionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($subscriptionOptionMock);

        $this->assertSame(
            $subscriptionOptionMock,
            $this->optionResolver->getSubscriptionOption($quoteItemMock, $planMock)
        );
    }

    public function getSubscriptionOptionDataProvider()
    {
        return [
            ['priceIncludesTax' => false],
            ['priceIncludesTax' => true],
        ];
    }

    /**
     * Test getSubscriptionOption method if item is simple product configuration
     */
    public function testGetSubscriptionOptionNotAbstractItem()
    {
        $planId = 1;
        $productId = 2;

        $planMock = $this->getMockForAbstractClass(PlanInterface::class);
        $planMock->expects($this->once())
            ->method('getPlanId')
            ->willReturn($planId);

        $productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);

        $itemMock = $this->getMockForAbstractClass(ItemInterface::class);
        $itemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);

        $subscriptionOptionMock = $this->getSubscriptionOptionMock(
            $planMock,
            $planId,
            $productId,
            0,
            0,
            0
        );

        $this->subscriptionOptionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($subscriptionOptionMock);

        $this->assertSame(
            $subscriptionOptionMock,
            $this->optionResolver->getSubscriptionOption($itemMock, $planMock)
        );
    }

    /**
     * @param ProductInterface|\PHPUnit_Framework_MockObject_MockObject $productMock
     * @param float $initialFee
     * @param float $trialPrice
     * @param float $regularPrice
     * @param bool $priceIncludesTax
     * @return Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getQuoteItemMock($productMock, $initialFee, $trialPrice, $regularPrice, $priceIncludesTax)
    {
        $quoteItemMock = $this->createPartialMock(
            Item::class,
            [
                'getProduct',
                'getBaseAwSarpInitialFee',
                'getBaseAwSarpTrialPrice',
                'getBaseAwSarpRegularPrice',
                'getBaseAwSarpInitialFeeInclTax',
                'getBaseAwSarpTrialPriceInclTax',
                'getBaseAwSarpRegularPriceInclTax'
            ]
        );
        $quoteItemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);
        if ($priceIncludesTax) {
            $quoteItemMock->expects($this->once())
                ->method('getBaseAwSarpInitialFeeInclTax')
                ->willReturn($initialFee);
            $quoteItemMock->expects($this->once())
                ->method('getBaseAwSarpTrialPriceInclTax')
                ->willReturn($trialPrice);
            $quoteItemMock->expects($this->once())
                ->method('getBaseAwSarpRegularPriceInclTax')
                ->willReturn($regularPrice);
        } else {
            $quoteItemMock->expects($this->once())
                ->method('getBaseAwSarpInitialFee')
                ->willReturn($initialFee);
            $quoteItemMock->expects($this->once())
                ->method('getBaseAwSarpTrialPrice')
                ->willReturn($trialPrice);
            $quoteItemMock->expects($this->once())
                ->method('getBaseAwSarpRegularPrice')
                ->willReturn($regularPrice);
        }

        return $quoteItemMock;
    }

    /**
     * Get subscriptionOption mock
     *
     * @param PlanInterface|\PHPUnit_Framework_MockObject_MockObject $planMock
     * @param int $planId
     * @param int $productId
     * @param float $initialFee
     * @param float $trialPrice
     * @param float $regularPrice
     * @return SubscriptionOptionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getSubscriptionOptionMock($planMock, $planId, $productId, $initialFee, $trialPrice, $regularPrice)
    {
        $subscriptionOptionMock = $this->getMockForAbstractClass(SubscriptionOptionInterface::class);
        $subscriptionOptionMock->expects($this->once())
            ->method('setPlan')
            ->with($planMock)
            ->willReturnSelf();
        $subscriptionOptionMock->expects($this->once())
            ->method('setPlanId')
            ->with($planId)
            ->willReturnSelf();
        $subscriptionOptionMock->expects($this->once())
            ->method('setProductId')
            ->with($productId)
            ->willReturnSelf();
        $subscriptionOptionMock->expects($this->once())
            ->method('setInitialFee')
            ->with($initialFee)
            ->willReturnSelf();
        $subscriptionOptionMock->expects($this->once())
            ->method('setTrialPrice')
            ->with($trialPrice)
            ->willReturnSelf();
        $subscriptionOptionMock->expects($this->once())
            ->method('setRegularPrice')
            ->with($regularPrice)
            ->willReturnSelf();

        return $subscriptionOptionMock;
    }
}
