<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Test\Unit\Model\Sales\Order\Item\Option;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Model\Sales\Order\Item\Option\Processor;
use Aheadworks\Sarp2\Api\Data\PlanInterfaceFactory;
use Aheadworks\Sarp2\Model\Plan\TitleResolver as PlanTitleResolver;
use Magento\Framework\Api\DataObjectHelper;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Model\Sales\Order\Item\Option\Processor
 */
class ProcessorTest extends TestCase
{
    const PLAN_NAME = 'Plan Name';
    const STORE_PLAN_NAME = 'Store Plan Name';
    const SUBSCRIPTION_PLAN = 'Subscription Plan';
    const OPTION_1 ='Option 1';
    const TEST_NAME = 'Test Plan Name';
    const OPTION_2 = 'Option 2';
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var PlanInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $planFactoryMock;

    /**
     * @var PlanTitleResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $planTitleResolverMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->planFactoryMock = $this->createPartialMock(
            PlanInterfaceFactory::class,
            ['create']
        );
        $this->planTitleResolverMock = $this->createPartialMock(
            PlanTitleResolver::class,
            ['getTitle']
        );
        $this->dataObjectHelperMock = $this->createPartialMock(
            DataObjectHelper::class,
            ['populateWithArray']
        );

        $this->processor = $objectManager->getObject(
            Processor::class,
            [
                'planFactory' => $this->planFactoryMock,
                'planTitleResolver' => $this->planTitleResolverMock,
                'dataObjectHelper' => $this->dataObjectHelperMock
            ]
        );
    }

    /**
     * Test isSubscription method
     *
     * @param array $options
     * @param bool $result
     * @dataProvider isSubscriptionDataProvider
     */
    public function testIsSubscription($options, $result)
    {
        $this->assertEquals($result, $this->processor->isSubscription($options));
    }

    /**
     * @return array
     */
    public function isSubscriptionDataProvider()
    {
        return [
            [
                'options' => [],
                'result' => false,
            ],
            [
                'options' => [
                    'aw_sarp2_subscription_plan' => null
                ],
                'result' => false,
            ],
            [
                'options' => [
                    'aw_sarp2_subscription_plan' => []
                ],
                'result' => true,
            ],
        ];
    }

    /**
     * Test getDetailedSubscriptionOptions method
     *
     * @param string $planName
     * @param string $planTitle
     * @param array $options
     * @param bool $isAdmin
     * @param array $result
     * @throws \Magento\Framework\Exception\LocalizedException
     * @dataProvider getDetailedSubscriptionOptionsDataProvider
     */
    public function testGetDetailedSubscriptionOptions($planName, $planTitle, $options, $isAdmin, $result)
    {
        $storeId = 1;
        if (isset($options['aw_sarp2_subscription_plan'])) {
            $planData = $options['aw_sarp2_subscription_plan'];

            $planMock = $this->createMock(PlanInterface::class);
            if (isset($planData['plan_id'])) {
                $planMock->expects($this->once())
                    ->method('getPlanId')
                    ->willReturn($planData['plan_id']);
            }
            $this->planFactoryMock->expects($this->once())
                ->method('create')
                ->willReturn($planMock);

            $this->dataObjectHelperMock->expects($this->once())
                ->method('populateWithArray')
                ->with($planMock, $planData, PlanInterface::class)
                ->willReturnSelf();

            if ($isAdmin) {
                $planMock->expects($this->once())
                    ->method('getName')
                    ->willReturn($planName);
            } else {
                $this->planTitleResolverMock->expects($this->once())
                    ->method('getTitle')
                    ->with($planMock, $storeId)
                    ->willReturn($planTitle);
            }
        }

        $this->assertEquals($result, $this->processor->getDetailedSubscriptionOptions($options, $storeId, $isAdmin));
    }

    /**
     * @return array
     */
    public function getDetailedSubscriptionOptionsDataProvider()
    {
        return [
            [
                'planName' => self::PLAN_NAME,
                'planTitle' => self::STORE_PLAN_NAME,
                'options' => [
                    'aw_sarp2_subscription_plan' => [
                        'plan_id' => 10
                    ]
                ],
                'isAdmin' => false,
                'result' => [
                    [
                        'label' =>__(self::SUBSCRIPTION_PLAN),
                        'value' => self::STORE_PLAN_NAME,
                        'aw_sarp2_subscription_plan' => 10
                    ]
                ]
            ],
            [
                'planName' => self::PLAN_NAME,
                'planTitle' => self::STORE_PLAN_NAME,
                'options' => [
                    'aw_sarp2_subscription_plan' => [
                        'plan_id' => 10
                    ]
                ],
                'isAdmin' => true,
                'result' => [
                    [
                        'label' =>__(self::SUBSCRIPTION_PLAN),
                        'value' => self::PLAN_NAME,
                        'aw_sarp2_subscription_plan' => 10
                    ]
                ]
            ],
            [
                'planName' => self::PLAN_NAME,
                'planTitle' => self::STORE_PLAN_NAME,
                'options' => [],
                'isAdmin' => false,
                'result' => []
            ],
            [
                'planName' => self::PLAN_NAME,
                'planTitle' => self::STORE_PLAN_NAME,
                'options' => [
                    'aw_sarp2_subscription_plan' => null
                ],
                'isAdmin' => false,
                'result' => []
            ],
        ];
    }

    /**
     * Test removeSubscriptionOptions method
     *
     * @param array $options
     * @param array $expectedResult
     * @dataProvider removeSubscriptionOptionsDataProvider
     */
    public function testRemoveSubscriptionOptions($options, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->processor->removeSubscriptionOptions($options));
    }

    /**
     * @return array
     */
    public function removeSubscriptionOptionsDataProvider()
    {
        return [
            [
                'options' => [],
                'expectedResult' => [],
            ],
            [
                'options' => [
                    0 => [
                        'label' => self::OPTION_1,
                        'value' => 1,
                    ],
                    1 => [
                        'label' => self::SUBSCRIPTION_PLAN,
                        'value' => self::TEST_NAME,
                        'aw_sarp2_subscription_plan' => 1,
                    ],
                    2 => [
                        'label' => self::OPTION_2,
                        'value' => 2,
                    ],
                ],
                'expectedResult' => [
                    0 => [
                        'label' => self::OPTION_1,
                        'value' => 1,
                    ],
                    2 => [
                        'label' => self::OPTION_2,
                        'value' => 2,
                    ],
                ],
            ],
            [
                'options' => [
                    0 => [
                        'label' => self::OPTION_1,
                        'value' => 1,
                    ],
                    1 => [
                        'label' => self::SUBSCRIPTION_PLAN,
                        'value' => self::TEST_NAME,
                        'aw_sarp2_subscription_plan' => 1,
                    ],
                ],
                'expectedResult' => [
                    0 => [
                        'label' => self::OPTION_1,
                        'value' => 1,
                    ],
                ],
            ],
            [
                'options' => [
                    0 => [
                        'label' => self::SUBSCRIPTION_PLAN,
                        'value' => self::TEST_NAME,
                        'aw_sarp2_subscription_plan' => 1,
                    ],
                    1 => [
                        'label' => self::OPTION_2,
                        'value' => 2,
                    ],
                ],
                'expectedResult' => [
                    1 => [
                        'label' => self::OPTION_2,
                        'value' => 2,
                    ],
                ],
            ],
        ];
    }
}
