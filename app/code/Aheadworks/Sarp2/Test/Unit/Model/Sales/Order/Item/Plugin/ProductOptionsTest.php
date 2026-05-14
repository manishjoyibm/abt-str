<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Test\Unit\Model\Sales\Order\Item\Plugin;

use Aheadworks\Sarp2\Model\Sales\Order\Item\Plugin\ProductOptions;
use Aheadworks\Sarp2\Model\Sales\Order\Item\Option\Processor as OrderItemOptionProcessor;
use Magento\Framework\App\State as AppState;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * Test for \Aheadworks\Sarp2\Model\Sales\Order\Item\Plugin\ProductOptions
 */
class ProductOptionsTest extends TestCase
{
    /**
     * @var ProductOptions
     */
    private $productOptions;

    /**
     * @var OrderItemOptionProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionProcessorMock;

    /**
     * @var AppState|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appStateMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->optionProcessorMock = $this->createPartialMock(
            OrderItemOptionProcessor::class,
            ['isSubscription', 'getDetailedSubscriptionOptions', 'removeSubscriptionOptions']
        );
        $this->appStateMock = $this->createPartialMock(
            AppState::class,
            ['getAreaCode']
        );
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->productOptions = $objectManager->getObject(
            ProductOptions::class,
            [
                'optionProcessor' => $this->optionProcessorMock,
                'appState' => $this->appStateMock,
                'storeManager' => $this->storeManagerMock,
            ]
        );
    }

    /**
     * Test afterGetProductOptions method
     *
     * @param string $areaCode
     * @param bool $isSubscription
     * @param array $options
     * @param array $detailedOptions
     * @param array $resultOptions
     * @dataProvider afterGetProductOptionsDataProvider
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testAfterGetProductOptions($areaCode, $isSubscription, $options, $detailedOptions, $resultOptions)
    {
        $storeId = 1;

        if ($isSubscription) {
            $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
            $storeMock->expects($this->once())
                ->method('getId')
                ->willReturn($storeId);
            $this->storeManagerMock->expects($this->once())
                ->method('getStore')
                ->willReturn($storeMock);

            $this->appStateMock->expects($this->once())
                ->method('getAreaCode')
                ->willReturn($areaCode);

            $this->optionProcessorMock->expects($this->once())
                ->method('isSubscription')
                ->with($options)
                ->willReturn($isSubscription);
            $this->optionProcessorMock->expects($this->once())
                ->method('getDetailedSubscriptionOptions')
                ->with($options)
                ->willReturn($detailedOptions);
            if (isset($options['options'])) {
                $this->optionProcessorMock->expects($this->once())
                    ->method('removeSubscriptionOptions')
                    ->with($options['options'])
                    ->willReturn($options['options']);
            }
        }

        $orderItemMock = $this->createPartialMock(OrderItem::class, []);

        $this->assertEquals($resultOptions, $this->productOptions->afterGetProductOptions($orderItemMock, $options));
    }

    /**
     * @return array
     */
    public function afterGetProductOptionsDataProvider()
    {
        $optionData = [
            'label' => 'Option',
            'value' => 123,
        ];

        return [
            [
                'areaCode' => 'frontend',
                'isSubscription' => true,
                'options' => [],
                'detailedOptions' => [
                    $optionData
                ],
                'resultOptions' => [
                    'options' => [
                        $optionData
                    ]
                ]
            ],
            [
                'areaCode' => 'adminhtml',
                'isSubscription' => true,
                'options' => [],
                'detailedOptions' => [
                    $optionData
                ],
                'resultOptions' => [
                    'options' => [
                        $optionData
                    ]
                ]
            ],
            [
                'areaCode' => 'frontend',
                'isSubscription' => true,
                'options' => [
                    'options' => []
                ],
                'detailedOptions' => [
                    $optionData
                ],
                'resultOptions' => [
                    'options' => [
                        $optionData
                    ]
                ]
            ],
            [
                'areaCode' => 'adminhtml',
                'isSubscription' => true,
                'options' => [
                    'options' => []
                ],
                'detailedOptions' => [
                    $optionData
                ],
                'resultOptions' => [
                    'options' => [
                        $optionData
                    ]
                ]
            ],
            [
                'areaCode' => 'frontend',
                'isSubscription' => false,
                'options' => [],
                'detailedOptions' => [],
                'resultOptions' => []
            ],
        ];
    }
}
