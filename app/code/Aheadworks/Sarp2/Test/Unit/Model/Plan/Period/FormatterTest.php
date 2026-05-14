<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Test\Unit\Model\Plan\Period;

use Aheadworks\Sarp2\Model\Plan\Period\Formatter;
use Aheadworks\Sarp2\Model\Plan\Source\BillingPeriod as BillingPeriodSource;
use Aheadworks\Sarp2\Model\Plan\Source\RepeatPayments as RepeatPaymentsSource;
use Aheadworks\Sarp2\Model\Plan\Source\RepeatPayments\Converter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Model\Plan\Period\Formatter
 */
class FormatterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @var BillingPeriodSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $billingPeriodSourceMock;

    /**
     * @var RepeatPaymentsSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repeatPaymentsSourceMock;

    /**
     * @var Converter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repeatPaymentsConverterMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->billingPeriodSourceMock = $this->createMock(BillingPeriodSource::class);
        $this->repeatPaymentsSourceMock = $this->createMock(RepeatPaymentsSource::class);
        $this->repeatPaymentsConverterMock = $this->createMock(Converter::class);

        $this->formatter = $objectManager->getObject(
            Formatter::class,
            [
                'billingPeriodSource' => $this->billingPeriodSourceMock,
                'repeatPaymentsSource' => $this->repeatPaymentsSourceMock,
                'repeatPaymentsConverter' => $this->repeatPaymentsConverterMock,
            ]
        );
    }

    /**
     * Test format method
     *
     * @param string $billingPeriod
     * @param int $billingFrequency
     * @param string $expectedResult
     * @dataProvider formatDataProvider
     */
    public function testFormat(
        $billingPeriod,
        $billingFrequency,
        $expectedResult
    ) {
        $repeatPaymentsOptions = [1 => 'Daily', 2 => 'Weekly'];
        $billingOptions = ['day' => 'Day', 'week' => 'Week', 'year' => 'Year'];
        $billingRepeatPaymentsMap = [['day', 1, 1], ['week', 1, 2]];

        $this->repeatPaymentsSourceMock->expects($this->any())
            ->method('getOptions')
            ->willReturn($repeatPaymentsOptions);
        $this->billingPeriodSourceMock->expects($this->any())
            ->method('getOptions')
            ->willReturn($billingOptions);
        $this->repeatPaymentsConverterMock->expects($this->once())
            ->method('toRepeatPayments')
            ->will($this->returnValueMap($billingRepeatPaymentsMap));

        $this->assertEquals(
            $expectedResult,
            $this->formatter->format($billingPeriod, $billingFrequency)
        );
    }

    /**
     * @return array
     */
    public function formatDataProvider()
    {
        return [
            ['day', 1, 'Daily'],
            ['day', 2, 'Every 2 Days'],
            ['week', 1, "Weekly"],
            ['week', 3, 'Every 3 Weeks'],
            ['year', 1, 'Every 1 Year'],
            ['year', 4, 'Every 4 Years'],
        ];
    }
}
