<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Test\Unit\Engine\DataResolver;

use Aheadworks\Sarp2\Engine\DataResolver\NextReattemptDate;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\DataResolver\NextReattemptDate
 */
class NextReattemptDateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NextReattemptDate
     */
    private $resolver;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->dateTimeMock = $this->createMock(DateTime::class);
        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with(
                $this->callback(
                    function ($argument) {
                        return $argument instanceof \DateTime;
                    }
                )
            )
            ->willReturnCallback(
                function ($date) {
                    return $date->format(DateTime::DATETIME_PHP_FORMAT);
                }
            );

        $this->resolver = $objectManager->getObject(
            NextReattemptDate::class,
            ['dateTime' => $this->dateTimeMock]
        );
    }

    /**
     * @param string $paymentDate
     * @param int $reattemptsCount
     * @param array $map
     * @param string $result
     * @dataProvider getDateNextDataProvider
     */
    public function testGetDateNext($paymentDate, $reattemptsCount, $map, $result)
    {
        $class = new \ReflectionClass($this->resolver);

        $reattemptsScheduleProperty = $class->getProperty('reattemptsSchedule');
        $reattemptsScheduleProperty->setAccessible(true);
        $reattemptsScheduleProperty->setValue($this->resolver, $map);

        $this->assertEquals($result, $this->resolver->getDateNext($paymentDate, $reattemptsCount));
    }

    /**
     * @param string $paymentDate
     * @param int $reattemptsCount
     * @param array $map
     * @param string $result
     * @dataProvider getLastDateDataProvider
     */
    public function testGetLastDate($paymentDate, $reattemptsCount, $map, $result)
    {
        $class = new \ReflectionClass($this->resolver);

        $toApiMapsProperty = $class->getProperty('reattemptsSchedule');
        $toApiMapsProperty->setAccessible(true);
        $toApiMapsProperty->setValue($this->resolver, $map);

        $this->assertEquals($result, $this->resolver->getLastDate($paymentDate, $reattemptsCount));
    }

    /**
     * @return array
     */
    public function getDateNextDataProvider()
    {
        return [
            ['2017-08-01 14:01:08', 0, [0 => 1, 1 => 2], '2017-08-02 14:01:08'],
            ['2017-08-01 14:01:08', 1, [0 => 1, 1 => 2], '2017-08-03 14:01:08']
        ];
    }

    /**
     * @return array
     */
    public function getLastDateDataProvider()
    {
        return [
            ['2017-08-01 14:01:08', 0, [0 => 1, 1 => 2], '2017-08-04 14:01:08'],
            ['2017-08-01 14:01:08', 1, [0 => 1, 1 => 2], '2017-08-03 14:01:08'],
            ['2017-08-01 14:01:08', 2, [0 => 1, 1 => 2], '2017-08-01 14:01:08']
        ];
    }
}
