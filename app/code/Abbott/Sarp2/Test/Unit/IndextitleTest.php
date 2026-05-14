<?php
namespace Abbott\Sarp2\Test\Unit;

use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\View\LayoutFactory;

class IndextitleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var (\Magento\Sales\Block\Order\View & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $salesinlineMock;
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    public $objectManager;
    /**
     * @var (\Magento\Customer\Model\Session & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $sessionMock;
    public $requestMock;
    public $layout;
    public $subject;
    public $page;
    public $profilemodel;
    /**
     * @var (\Magento\Framework\View\Page\Title & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $titlepage;
    public $layoutMock;
    public $title;
    public $subscriptioninlineMock;
    public $storeManagerMock;
    public $indexPluginMock;
    protected $customerSession;
    protected $_layoutFactory;

    /**
     * @return void
     */
    public function setUp()
    {
        
        $this->salesinlineMock = $this->createPartialMock(
            \Magento\Sales\Block\Order\View::class,
            ['getOrder']
        );

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
      
        $this->sessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
     
        $this->requestMock = $this->createMock(RequestInterface::class);

        $this->layout = $this->createMock(LayoutFactory::class);

        $this->subject = $this->createMock(\Aheadworks\Sarp2\Controller\Profile\Edit\Index::class);

        $this->page = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->profilemodel = $this->getMockBuilder(\Aheadworks\Sarp2\Api\Data\ProfileInterface::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->titlepage = $this->createPartialMock(\Magento\Framework\View\Page\Title::class, ['getTitle']);

        $this->layoutMock = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['createBlock', 'toHtml']);

        $this->title = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->subscriptioninlineMock = $this->createPartialMock(
            \Magento\Sales\Block\Order\View::class,
            ['getProfile']
        );

        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);


        $this->indexPluginMock = $this->objectManager->getObject(
            \Abbott\Sarp2\Plugin\Indextitle::class,
            [
              'sessionSession' => $this->sessionMock,
              'requestInterface' => $this->requestMock,
              'layoutFactory' => $this->layout,
              'storeManager' => $this->storeManagerMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testafterExecute()
    {
        $profile_id = 12;
        $this->requestMock->expects($this->any())->method('getParam')->with('profile_id')->willReturn($this->returnValue($profile_id));

        $this->layoutMock->expects($this->once())->method('createBlock')->with('Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View')->will($this->returnValue($this->subscriptioninlineMock));

        $this->layout->expects($this->once())->method('create')->will($this->returnValue($this->layoutMock));

        $this->subscriptioninlineMock->expects($this->any())->method('getProfile')->will($this->returnValue($this->profilemodel));

        $this->profilemodel->expects($this->any())->method('getIncrementId')->willReturn('1234');



        $store = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);

        $store->expects($this->once())
        ->method('getCode')
        ->willReturn('new_similac');

        $this->storeManagerMock->expects($this->any())
        ->method('getStore')
        ->willReturn($store);



        $this->title->expects($this->once())
        ->method('set')
        ->with('Subscription #1234');

        $config = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $config->expects($this->once())
            ->method('getTitle')
            ->willReturn($this->title);

        $this->page->expects($this->once())
            ->method('getConfig')
            ->willReturn($config);

        $this->assertEquals($this->page, $this->indexPluginMock->afterExecute($this->subject, $this->page));
    }
}
