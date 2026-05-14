<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Test\Unit\Model\Product\Subscription;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\PriceCalculation;
use Aheadworks\Sarp2\Model\Plan\Source\PriceRounding;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Test for \Aheadworks\Sarp2\Model\Product\Subscription\PriceCalculation
 */
class PriceCalculationTest extends \PHPUnit\Framework\TestCase
{
    const MESSAGE = 'No such entity!';
    /**
     * @var PriceCalculation
     */
    private $calculation;

    /**
     * @var PlanRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $planRepositoryMock;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepositoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->planRepositoryMock = $this->createMock(PlanRepositoryInterface::class);
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);

        $this->calculation = $objectManager->getObject(
            PriceCalculation::class,
            [
                'planRepository' => $this->planRepositoryMock,
                'productRepository' => $this->productRepositoryMock,
            ]
        );
    }

    /**
     * Test getAutoTrialPrice method
     *
     * @param float $price
     * @param float $percent
     * @param int $rounding
     * @param float $expectedResult
     * @dataProvider calculateAndRoundDataProvider
     * @throws NoSuchEntityException
     */
    public function testGetAutoTrialPrice($price, $percent, $rounding, $expectedResult)
    {
        $productId = 1;
        $planId = 10;

        $productMock = $this->createMock(ProductInterface::class);
        $productMock->expects($this->once())
            ->method('getPrice')
            ->willReturn($price);
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($productMock);

        $planMock = $this->createMock(PlanInterface::class);
        $planMock->expects($this->once())
            ->method('getTrialPricePatternPercent')
            ->willReturn($percent);
        $planMock->expects($this->once())
            ->method('getPriceRounding')
            ->willReturn($rounding);
        $this->planRepositoryMock->expects($this->once())
            ->method('get')
            ->with($planId)
            ->willReturn($planMock);

        $this->assertEquals($expectedResult, $this->calculation->getAutoTrialPrice($productId, $planId));
    }

    /**
     * Test getAutoTrialPrice method if no product found
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity!
     */
    public function testGetAutoTrialPriceNoProduct()
    {
        $productId = 1;
        $planId = 10;

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willThrowException(new NoSuchEntityException(__(self::MESSAGE)));

        $this->calculation->getAutoTrialPrice($productId, $planId);
    }

    /**
     * Test getAutoTrialPrice method if no plan found
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity!
     */
    public function testGetAutoTrialPriceNoPlan()
    {
        $productId = 1;
        $planId = 10;

        $productMock = $this->createMock(ProductInterface::class);
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($productMock);

        $this->planRepositoryMock->expects($this->once())
            ->method('get')
            ->with($planId)
            ->willThrowException(new NoSuchEntityException(__(self::MESSAGE)));

        $this->calculation->getAutoTrialPrice($productId, $planId);
    }

    /**
     * Test getAutoRegularPrice method
     *
     * @param float $price
     * @param float $percent
     * @param int $rounding
     * @param float $expectedResult
     * @dataProvider calculateAndRoundDataProvider
     * @throws NoSuchEntityException
     */
    public function testGetAutoRegularPrice($price, $percent, $rounding, $expectedResult)
    {
        $productId = 1;
        $planId = 10;

        $productMock = $this->createMock(ProductInterface::class);
        $productMock->expects($this->once())
            ->method('getPrice')
            ->willReturn($price);
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($productMock);

        $planMock = $this->createMock(PlanInterface::class);
        $planMock->expects($this->once())
            ->method('getRegularPricePatternPercent')
            ->willReturn($percent);
        $planMock->expects($this->once())
            ->method('getPriceRounding')
            ->willReturn($rounding);
        $this->planRepositoryMock->expects($this->once())
            ->method('get')
            ->with($planId)
            ->willReturn($planMock);

        $this->assertEquals($expectedResult, $this->calculation->getAutoRegularPrice($productId, $planId));
    }

    /**
     * Test getAutoRegularPrice method if no product found
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity!
     */
    public function testGetAutoRegularPriceNoProduct()
    {
        $productId = 1;
        $planId = 10;

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willThrowException(new NoSuchEntityException(__(self::MESSAGE)));

        $this->calculation->getAutoRegularPrice($productId, $planId);
    }

    /**
     * Test getAutoRegularPrice method if no plan found
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity!
     */
    public function testGetAutoRegularPriceNoPlan()
    {
        $productId = 1;
        $planId = 10;

        $productMock = $this->createMock(ProductInterface::class);
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($productMock);

        $this->planRepositoryMock->expects($this->once())
            ->method('get')
            ->with($planId)
            ->willThrowException(new NoSuchEntityException(__(self::MESSAGE)));

        $this->calculation->getAutoRegularPrice($productId, $planId);
    }

    /**
     * Test calculateAndRound method
     *
     * @param float $price
     * @param float $percent
     * @param int $rounding
     * @param float $expectedResult
     * @dataProvider calculateAndRoundDataProvider
     * @throws \ReflectionException
     */
    public function testCalculateAndRound($price, $percent, $rounding, $expectedResult)
    {
        $class = new \ReflectionClass($this->calculation);
        $method = $class->getMethod('calculateAndRound');
        $method->setAccessible(true);

        $this->assertEquals(
            $expectedResult,
            $method->invokeArgs($this->calculation, [$price, $percent, $rounding])
        );
    }

    /**
     * @return array
     */
    public function calculateAndRoundDataProvider()
    {
        return [
            [10.00, 90.00, PriceRounding::UP_TO_XX_99, 9.99],
            [10.00, 90.00, PriceRounding::UP_TO_XX_90, 9.90],
            [10.00, 90.00, PriceRounding::UP_TO_X9_00, 9],
            [20.00, 90.00, PriceRounding::UP_TO_X9_00, 19],
            [10.00, 90.00, PriceRounding::DOWN_TO_XX_99, 8.99],
            [10.00, 90.00, PriceRounding::DOWN_TO_XX_90, 8.90],
            [10.00, 90.00, PriceRounding::DOWN_TO_X9_00, 0.01],
            [20.00, 90.00, PriceRounding::DOWN_TO_X9_00, 9],
            [34.00, 90.00, PriceRounding::UP_TO_XX_99, 30.99],
            [34.00, 90.00, PriceRounding::UP_TO_XX_90, 30.90],
            [34.00, 90.00, PriceRounding::UP_TO_X9_00, 29],
            [34.00, 90.00, PriceRounding::DOWN_TO_XX_99, 29.99],
            [34.00, 90.00, PriceRounding::DOWN_TO_XX_90, 29.9],
            [34.00, 90.00, PriceRounding::DOWN_TO_X9_00, 29.00],
            [128.00, 90.00, PriceRounding::UP_TO_X9_00, 119],
            [128.00, 90.00, PriceRounding::UP_TO_XX_99, 115.99],
            [128.00, 90.00, PriceRounding::UP_TO_XX_90, 115.9],
            [128.00, 90.00, PriceRounding::DOWN_TO_X9_00, 109],
            [128.00, 90.00, PriceRounding::DOWN_TO_XX_90, 114.90],
            [128.00, 90.00, PriceRounding::DOWN_TO_XX_99, 114.99],
            [1234.00, 90.00, PriceRounding::UP_TO_X9_00, 1119],
            [1234.00, 90.00, PriceRounding::DOWN_TO_X9_00, 1109],
            [1234.00, 80.00, PriceRounding::UP_TO_X9_00, 989],
            [1234.00, 80.00, PriceRounding::DOWN_TO_X9_00, 979],
            [1234.00, 81.00, PriceRounding::UP_TO_X9_00, 1009],
            [1234.00, 81.00, PriceRounding::UP_TO_XX_99, 999.99],
            [1234.00, 81.00, PriceRounding::UP_TO_XX_90, 999.90],
            [1234.00, 81.00, PriceRounding::DOWN_TO_X9_00, 999],
            [1234.00, 81.00, PriceRounding::DOWN_TO_XX_90, 998.90],
            [1234.00, 81.00, PriceRounding::DOWN_TO_XX_99, 998.99],
        ];
    }
}
