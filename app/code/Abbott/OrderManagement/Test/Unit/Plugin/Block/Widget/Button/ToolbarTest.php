<?php
namespace Abbott\OrderManagement\Test\Unit\Plugin\Block\Widget\Button;

use Abbott\OrderManagement\Helper\Data;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Toolbar;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\TestCase;

class ToolbarTest extends TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    public $objectManager;
    public $helperMock;
    public $contextMock;
    public $buttonMock;
    public $toolMock;
    protected $orderHelperMock;

    public function setUp() : void
    {

        $this->orderHelperMock = $this->getMockBuilder(Data::class)->disableOriginalConstructor()->setMethods(
            [
                'checkIsProgressiveAndBuyersRemorse','getTime'
            ]
        )
        ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->helperMock = $this->objectManager->getObject(
            \Abbott\OrderManagement\Plugin\Block\Widget\Button\Toolbar::class,
            [
            'helper' => $this->orderHelperMock
            ]
        );
    }
    public function testBeforePushButtons()
    {

        $dateString = '01.01.1970 00:01';
        $checkProgressiveAndBuyersRemorse = ['is_progressive'=>1,'is_cancel_order'=>1];

         $this->contextMock = $this->getMockBuilder(View::class)->setMethods(
             ['getOrder']
         )->disableOriginalConstructor()->getMock();

        $this->buttonMock = $this->getMockBuilder(ButtonList::class)->disableOriginalConstructor()->getMock();

        $this->toolMock = $this->getMockBuilder(Toolbar::class)->disableOriginalConstructor()->getMock();

        $orderMock = $this->getMockBuilder(Order::class)
        ->disableOriginalConstructor()
        ->setMethods(
            [
                'getId',
                'getIncrementId',
                'getStoreId',
                'getCustomerId',
                'getCreatedAt',
                'getCustomerName',
                '__wakeup',
                'getEntityId'
            ]
        )
        ->getMock();

        $this->contextMock->method('getOrder')
        ->willReturn($orderMock);

        $orderMock->method('getCreatedAt')
        ->willReturn($dateString);

        $this->orderHelperMock->expects($this->once())->method('getTime')
        ->willReturn(strtotime(date('Y-m-d H:i:s')));

        $this->orderHelperMock->expects($this->once())->method('checkIsProgressiveAndBuyersRemorse')
        ->willReturn($checkProgressiveAndBuyersRemorse);

        $return = [$this->contextMock,$this->buttonMock];

        $this->assertEquals($return, $this->helperMock->beforePushButtons(
            $this->toolMock,
            $this->contextMock,
            $this->buttonMock
        ));
    }

    public function testBeforePushButtonsFalse()
    {

        $this->contextMock = $this->getMockBuilder(AbstractBlock::class)->setMethods(
            ['getOrder']
        )->disableOriginalConstructor()->getMock();

        $this->buttonMock = $this->getMockBuilder(ButtonList::class)->disableOriginalConstructor()->getMock();

        $this->toolMock = $this->getMockBuilder(Toolbar::class)->disableOriginalConstructor()->getMock();

        $return = [$this->contextMock,$this->buttonMock];

        $this->assertEquals($return, $this->helperMock->beforePushButtons(
            $this->toolMock,
            $this->contextMock,
            $this->buttonMock
        ));
    }

    public function testBeforePushButtonsisCancelzero()
    {
        $dateString = '01.01.1970 00:01';
        $checkProgressiveAndBuyersRemorse = ['is_progressive'=>1,'is_cancel_order'=>0];

         $this->contextMock = $this->getMockBuilder(View::class)->setMethods(
             ['getOrder']
         )->disableOriginalConstructor()->getMock();

        $this->buttonMock = $this->getMockBuilder(ButtonList::class)->disableOriginalConstructor()->getMock();

        $this->toolMock = $this->getMockBuilder(Toolbar::class)->disableOriginalConstructor()->getMock();

        $orderMock = $this->getMockBuilder(Order::class)
        ->disableOriginalConstructor()
        ->setMethods(
            [
                'getId',
                'getIncrementId',
                'getStoreId',
                'getCustomerId',
                'getCreatedAt',
                'getCustomerName',
                '__wakeup',
                'getEntityId',
                'getStatus',
                'getShippingMethod'
            ]
        )
        ->getMock();

        $this->contextMock->method('getOrder')
        ->willReturn($orderMock);

        $orderMock->method('getCreatedAt')
        ->willReturn($dateString);

        $this->orderHelperMock->expects($this->once())->method('getTime')
        ->willReturn(strtotime(date('Y-m-d H:i:s')));

        $this->orderHelperMock->expects($this->once())->method('checkIsProgressiveAndBuyersRemorse')
        ->willReturn($checkProgressiveAndBuyersRemorse);

        $this->buttonMock->expects($this->once())->method('remove')->with('order_cancel')->willReturnSelf();

        $return = [$this->contextMock,$this->buttonMock];

        $this->assertEquals($return, $this->helperMock->beforePushButtons($this->toolMock, $this->contextMock, $this->buttonMock));
    }
}
