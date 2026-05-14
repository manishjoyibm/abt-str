<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Generator;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\DataResolver\NextPaymentDate;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Evaluation\PaymentDetails;
use Aheadworks\Sarp2\Engine\Payment\Evaluation\PaymentDetailsFactory;
use Aheadworks\Sarp2\Engine\Payment\Generator\Evaluation;
use Aheadworks\Sarp2\Engine\Payment\ScheduleInterface;
use Aheadworks\Sarp2\Model\Plan\Source\BillingPeriod;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Generator\Evaluation
 */
class EvaluationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Billing period
     */
    const PERIOD = BillingPeriod::DAY;

    /**
     * Billing frequency
     */
    const FREQUENCY = 1;

    /**
     * @var Evaluation
     */
    private $evaluation;

    /**
     * @var CoreDate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreDateMock;

    /**
     * @var NextPaymentDate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nextPaymentDateMock;

    /**
     * @var PaymentDetailsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $detailsFactoryMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->coreDateMock = $this->createMock(CoreDate::class);
        $this->nextPaymentDateMock = $this->createMock(NextPaymentDate::class);
        $this->detailsFactoryMock = $this->createMock(PaymentDetailsFactory::class);
        $this->evaluation = $objectManager->getObject(
            Evaluation::class,
            [
                'coreDate' => $this->coreDateMock,
                'nextPaymentDate' => $this->nextPaymentDateMock,
                'detailsFactory' => $this->detailsFactoryMock
            ]
        );
    }

    /**
     * @param ScheduleInterface|\PHPUnit_Framework_MockObject_MockObject $scheduleMock
     * @param ProfileInterface|\PHPUnit_Framework_MockObject_MockObject $profileMock
     * @param string $currentDate
     * @param string|null $lastPaymentDate
     * @param array $expectedDetailsData
     * @dataProvider evaluateDataProvider
     */
    public function testEvaluate(
        $scheduleMock,
        $profileMock,
        $currentDate,
        $lastPaymentDate,
        $expectedDetailsData
    ) {
        $this->coreDateMock->expects($this->atLeast(2))
            ->method('gmtTimestamp')
            ->willReturnCallback(
                function ($input) {
                    /** @var \DateTimeInterface $input */
                    return $input->getTimestamp();
                }
            );
        if ($lastPaymentDate) {
            $this->nextPaymentDateMock->expects($this->once())
                ->method('getDateNext')
                ->with(
                    $lastPaymentDate,
                    self::PERIOD,
                    self::FREQUENCY
                )
                ->willReturnCallback(
                    function ($input) {
                        return (new \DateTime($input))
                            ->modify('+1 day')
                            ->format('Y-m-d H:i:s');
                    }
                );
        }

        $expectedResult = [];
        $index = 0;
        foreach ($expectedDetailsData as $data) {
            $detailsMock = $this->createMock(PaymentDetails::class);
            $this->detailsFactoryMock->expects($this->at($index))
                ->method('create')
                ->with($data)
                ->willReturn($detailsMock);
            $expectedResult[] = $detailsMock;
        }

        $this->assertEquals(
            $expectedResult,
            $this->evaluation->evaluate($scheduleMock, $profileMock, $currentDate, $lastPaymentDate)
        );
    }

    /**
     * @return array
     */
    public function evaluateDataProvider()
    {
        return [
            [
                $this->createConfiguredMock(ScheduleInterface::class, ['isInitialPaid' => false]),
                $this->createConfiguredMock(
                    ProfileInterface::class,
                    [
                        'getProfileDefinition' => $this->createConfiguredMock(
                            PlanDefinitionInterface::class,
                            ['getIsInitialFeeEnabled' => true]
                        ),
                        'getStartDate' => '2018-08-02 12:00:00',
                        'getInitialGrandTotal' => 10.00,
                        'getBaseInitialGrandTotal' => 15.00
                    ]
                ),
                '2018-08-01 12:00:00',
                null,
                [
                    [
                        'paymentPeriod' => PaymentInterface::PERIOD_INITIAL,
                        'paymentType' => PaymentInterface::TYPE_PLANNED,
                        'date' => '2018-08-02 12:00:00',
                        'totalAmount' => 10.00,
                        'baseTotalAmount' => 15.00
                    ]
                ]
            ],
            [
                $this->createConfiguredMock(ScheduleInterface::class, ['isInitialPaid' => false]),
                $this->createConfiguredMock(
                    ProfileInterface::class,
                    [
                        'getProfileDefinition' => $this->createConfiguredMock(
                            PlanDefinitionInterface::class,
                            ['getIsInitialFeeEnabled' => false]
                        ),
                        'getStartDate' => '2018-08-02 12:00:00'
                    ]
                ),
                '2018-08-01 12:00:00',
                null,
                []
            ],
            [
                $this->createConfiguredMock(ScheduleInterface::class, ['isInitialPaid' => true]),
                $this->createConfiguredMock(
                    ProfileInterface::class,
                    [
                        'getProfileDefinition' => $this->createConfiguredMock(
                            PlanDefinitionInterface::class,
                            ['getIsInitialFeeEnabled' => true]
                        ),
                        'getStartDate' => '2018-08-02 12:00:00'
                    ]
                ),
                '2018-08-01 12:00:00',
                null,
                []
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'getPeriod' => self::PERIOD,
                        'getFrequency' => self::FREQUENCY,
                        'isInitialPaid' => true,
                        'getTrialTotalCount' => 1,
                        'getTrialCount' => 0
                    ]
                ),
                $this->createConfiguredMock(
                    ProfileInterface::class,
                    [
                        'getProfileDefinition' => $this->createConfiguredMock(
                            PlanDefinitionInterface::class,
                            ['getIsInitialFeeEnabled' => true]
                        ),
                        'getStartDate' => '2018-08-01 12:00:00',
                        'getTrialGrandTotal' => 20.00,
                        'getBaseTrialGrandTotal' => 30.00
                    ]
                ),
                '2018-08-02 12:00:00',
                '2018-08-01 12:00:00',
                [
                    [
                        'paymentPeriod' => PaymentInterface::PERIOD_TRIAL,
                        'paymentType' => PaymentInterface::TYPE_PLANNED,
                        'date' => '2018-08-02 12:00:00',
                        'totalAmount' => 20.00,
                        'baseTotalAmount' => 30.00
                    ]
                ]
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'getPeriod' => self::PERIOD,
                        'getFrequency' => self::FREQUENCY,
                        'isInitialPaid' => true,
                        'getTrialTotalCount' => 1,
                        'getTrialCount' => 1,
                        'getRegularTotalCount' => 1,
                        'getRegularCount' => 0
                    ]
                ),
                $this->createConfiguredMock(
                    ProfileInterface::class,
                    [
                        'getProfileDefinition' => $this->createConfiguredMock(
                            PlanDefinitionInterface::class,
                            ['getIsInitialFeeEnabled' => true]
                        ),
                        'getStartDate' => '2018-08-01 12:00:00',
                        'getRegularGrandTotal' => 50.00,
                        'getBaseRegularGrandTotal' => 75.00
                    ]
                ),
                '2018-08-02 12:00:00',
                '2018-08-01 12:00:00',
                [
                    [
                        'paymentPeriod' => PaymentInterface::PERIOD_REGULAR,
                        'paymentType' => PaymentInterface::TYPE_PLANNED,
                        'date' => '2018-08-02 12:00:00',
                        'totalAmount' => 50.00,
                        'baseTotalAmount' => 75.00
                    ]
                ]
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'getPeriod' => self::PERIOD,
                        'getFrequency' => self::FREQUENCY,
                        'isInitialPaid' => true,
                        'getTrialTotalCount' => 0,
                        'getRegularTotalCount' => 1,
                        'getRegularCount' => 0
                    ]
                ),
                $this->createConfiguredMock(
                    ProfileInterface::class,
                    [
                        'getProfileDefinition' => $this->createConfiguredMock(
                            PlanDefinitionInterface::class,
                            ['getIsInitialFeeEnabled' => true]
                        ),
                        'getStartDate' => '2018-08-01 12:00:00',
                        'getRegularGrandTotal' => 50.00,
                        'getBaseRegularGrandTotal' => 75.00
                    ]
                ),
                '2018-08-02 12:00:00',
                '2018-08-01 12:00:00',
                [
                    [
                        'paymentPeriod' => PaymentInterface::PERIOD_REGULAR,
                        'paymentType' => PaymentInterface::TYPE_PLANNED,
                        'date' => '2018-08-02 12:00:00',
                        'totalAmount' => 50.00,
                        'baseTotalAmount' => 75.00
                    ]
                ]
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'getPeriod' => self::PERIOD,
                        'getFrequency' => self::FREQUENCY,
                        'isInitialPaid' => true,
                        'getTrialTotalCount' => 0,
                        'getRegularTotalCount' => 0
                    ]
                ),
                $this->createConfiguredMock(
                    ProfileInterface::class,
                    [
                        'getProfileDefinition' => $this->createConfiguredMock(
                            PlanDefinitionInterface::class,
                            ['getIsInitialFeeEnabled' => true]
                        ),
                        'getStartDate' => '2018-08-01 12:00:00',
                        'getRegularGrandTotal' => 50.00,
                        'getBaseRegularGrandTotal' => 75.00
                    ]
                ),
                '2018-08-02 12:00:00',
                '2018-08-01 12:00:00',
                [
                    [
                        'paymentPeriod' => PaymentInterface::PERIOD_REGULAR,
                        'paymentType' => PaymentInterface::TYPE_PLANNED,
                        'date' => '2018-08-02 12:00:00',
                        'totalAmount' => 50.00,
                        'baseTotalAmount' => 75.00
                    ]
                ]
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'getPeriod' => self::PERIOD,
                        'getFrequency' => self::FREQUENCY,
                        'isInitialPaid' => true,
                        'getTrialTotalCount' => 0,
                        'getRegularTotalCount' => 1,
                        'getRegularCount' => 1
                    ]
                ),
                $this->createConfiguredMock(
                    ProfileInterface::class,
                    [
                        'getProfileDefinition' => $this->createConfiguredMock(
                            PlanDefinitionInterface::class,
                            ['getIsInitialFeeEnabled' => true]
                        ),
                        'getStartDate' => '2018-08-01 12:00:00'
                    ]
                ),
                '2018-08-02 12:00:00',
                '2018-08-01 12:00:00',
                []
            ],
            [
                $this->createConfiguredMock(
                    ScheduleInterface::class,
                    [
                        'getPeriod' => self::PERIOD,
                        'getFrequency' => self::FREQUENCY
                    ]
                ),
                $this->createConfiguredMock(
                    ProfileInterface::class,
                    ['getStartDate' => '2018-08-01 12:00:00']
                ),
                '2018-08-01 12:00:00',
                '2018-08-01 12:00:00',
                []
            ]
        ];
    }
}
