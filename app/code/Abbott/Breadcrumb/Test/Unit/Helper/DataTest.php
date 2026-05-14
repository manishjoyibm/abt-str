<?php
namespace Abbott\Breadcrumb\Test\Unit\Helper;

use Magento\Framework\View\LayoutFactory;

class DataTest extends \PHPUnit\Framework\TestCase
{

    public $layoutMock;
    /**
     * @var (\Abbott\MyAccount\Helper\Data & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $accountHelperMock;
    /**
     * @var (\Abbott\CustomerTransistion\Helper\Data & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $custometranshelperMock;
    /**
     * @var \Magento\Sales\Block\Order\View\
     */
    protected $salesinlineMock;
    /**
     * @var \Magento\Sales\Block\Order\View\
     */
    protected $subscriptioninlineMock;
    /**
     * @var \Magento\Rma\Block\Adminhtml\Rma\Edit\
     */
    protected $rmainlineMock;
    /**
     * @var \Magento\Sales\Model\Order\
     */
    protected $ordermodel;
    /**
     * @var \Aheadworks\Sarp2\Api\Data\ProfileInterface\
     */
    protected $profilemodel;
    /**
     * @var \Magento\Rma\Api\Data\RmaInterface\
     */
    protected $rmamodel;
    /**
     * @var \Magento\Framework\App\RequestInterface\
     */
    protected $requestMock;
    /**
     * @var \Abbott\Breadcrumb\Helper\Data\
     */
    protected $helper;
    /**
     * @var \Magento\Framework\View\LayoutFactory\
     */
    protected $layout;
    /**
     * @return void
     */
    protected function setUp()
    {
        $this->layout = $this->getMockBuilder(\Magento\Framework\View\LayoutFactory::class, ['createBlock'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutMock = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['createBlock', 'toHtml']);
        $this->salesinlineMock = $this->createPartialMock(
            \Magento\Sales\Block\Order\View::class,
            ['getOrder']
        );
            $this->subscriptioninlineMock = $this->createPartialMock(
                \Magento\Sales\Block\Order\View::class,
                ['getProfile']
            );
            
            $this->rmainlineMock = $this->createPartialMock(
                \Magento\Rma\Block\Adminhtml\Rma\Edit::class,
                ['getRma']
            );

        $this->ordermodel = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
        ->disableOriginalConstructor()
        ->getMock();
        $this->profilemodel = $this->getMockBuilder(\Aheadworks\Sarp2\Api\Data\ProfileInterface::class)
        ->disableOriginalConstructor()
        ->getMock();
 
        $this->rmamodel = $this->getMockBuilder(\Magento\Rma\Api\Data\RmaInterface::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
        ->getMock();

        $this->accountHelperMock = $this->getMockBuilder(\Abbott\MyAccount\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->custometranshelperMock = $this->getMockBuilder(\Abbott\CustomerTransistion\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
    
        $this->helper = new \Abbott\Breadcrumb\Helper\Data($this->layout, $this->requestMock, $this->accountHelperMock, $this->custometranshelperMock);
    }

    /**
     * @return void
     */
    public function testGetLastCruminfonorder()
    {
        $incrementId = '1234';
        $order_id = 2;

        $this->requestMock->expects($this->any())->method('getParam')->with('order_id')->willReturn($this->returnValue($order_id));

        $this->ordermodel->expects($this->any())->method('getIncrementId')->willReturn('1234');

        $this->salesinlineMock->expects($this->any())->method('getOrder')->will($this->returnValue($this->ordermodel));

        $this->layoutMock->expects($this->once())->method('createBlock')->will($this->returnValue($this->salesinlineMock));

        $this->layout->expects($this->once())->method('create')->will($this->returnValue($this->layoutMock));
       
            $this->assertEquals($incrementId, $this->helper->GetLastCruminfo());
    }
    /**
     * @return void
     */
    public function testGetLastCruminfonprofile()
    {
        $incrementId = false;
            $this->assertEquals($incrementId, $this->helper->GetLastCruminfo());
    }
    /**
     * @return void
     */
    public function testGetLastCruminfonnull()
    {
        $incrementId = 1234;
        $this->requestMock->expects($this->at(1))->method('getParam')->with('profile_id')->will($this->returnValue(3));
  
        $this->profilemodel->expects($this->any())->method('getIncrementId')->willReturn('1234');

        $this->subscriptioninlineMock->expects($this->any())->method('getProfile')->will($this->returnValue($this->profilemodel));

        $this->layoutMock->expects($this->once())->method('createBlock')->will($this->returnValue($this->subscriptioninlineMock));

        $this->layout->expects($this->once())->method('create')->will($this->returnValue($this->layoutMock));
        $this->assertEquals($incrementId, $this->helper->GetLastCruminfo());
    }
    /**
     * @return void
     */
    public function testGetLastCruminforeturn()
    {
        $incrementId = 12345;
        $this->requestMock->expects($this->at(2))->method('getParam')->with('entity_id')->will($this->returnValue(4));
  
        $this->rmamodel->expects($this->any())->method('getIncrementId')->willReturn('12345');

        $this->rmainlineMock->expects($this->any())->method('getRma')->will($this->returnValue($this->rmamodel));

        $this->layoutMock->expects($this->once())->method('createBlock')->will($this->returnValue($this->rmainlineMock));

        $this->layout->expects($this->once())->method('create')->will($this->returnValue($this->layoutMock));
            $this->assertEquals($incrementId, $this->helper->GetLastCruminfo());
    }
}
