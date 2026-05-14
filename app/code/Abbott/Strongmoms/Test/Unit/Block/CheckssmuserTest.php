<?php
namespace Abbott\Strongmoms\Test\Unit\Block;

use Magento\Framework\View\LayoutFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CheckssmuserTest extends \PHPUnit\Framework\TestCase
{
    public $helperMock;
    /**
     * @var (\Magento\Framework\UrlInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $urlMock;
    public $helper;
    /**
     * @var \Magento\Framework\App\Helper\Context\
     */
    protected $contextMock;
     /**
      * @var \Magento\Customer\Model\Session\
      */
    protected $customerSessionMock;
     /**
      * @var \Magento\Store\Model\StoreManagerInterface\
      */
    protected $storeManagerMock;
    const IS_SIMILAC_SSM = "similac-ssm";
    /**
     * @return void
     */
    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->helperMock = $this->getMockBuilder(\Abbott\Strongmoms\Helper\Data::class)
        ->disableOriginalConstructor()->getMock();

        $this->urlMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
        ->disableOriginalConstructor()->getMock();

        $this->helper = $this->getMockBuilder(
            \Abbott\Strongmoms\Block\Checkssmuser::class
        )
            ->setConstructorArgs(
                [
                    $this->contextMock,
                    $this->helperMock,
                    $this->urlMock
                ]
            )->setMethods([
                    'getStoreId'
            ])
            ->getMock();
    }

    /**
     * @return bool
     */
    public function testCheckUserIsSsm()
    {
        $this->helperMock->expects($this->any())
        ->method('isSSM')
        ->willReturn(true);
        $this->assertEquals(true, $this->helper->checkUserIsSsm());
    }

    /**
     * @return int
     */
    public function testGetOrderCounttrue()
    {
        $ordercount = true;
        $this->helperMock->expects($this->any())
        ->method('getSsmUserOrderCount')
        ->willReturn($ordercount);
        $this->assertEquals($ordercount, $this->helper->getOrderCount());
    }
    /**
     * @return int
     */
    public function testGetOrderCountfalse()
    {
        $ordercount = false;
        $this->helperMock->expects($this->any())
        ->method('getSsmUserOrderCount')
        ->willReturn($ordercount);
        $this->assertEquals($ordercount, $this->helper->getOrderCount());
    }
    
    /**
     * @return string
     */
    public function testGetSsmUserNotes()
    {
        $ssmUserNote = 'test';

        $this->helperMock->expects($this->any())
        ->method('getSsmUserConfig')
        ->willReturn($ssmUserNote);
        
        $this->assertEquals($ssmUserNote, $this->helper->getSsmUserNotes());
    }

    /**
     * @return string
     */
    public function testGetSubscrptionUserNotes()
    {
        $subscriptionUserNote = 'test';

        $this->helperMock->expects($this->any())
        ->method('getsubscriptionUserConfig')
        ->willReturn($subscriptionUserNote);

        $this->helperMock->expects($this->any())
        ->method('getuserSubscription')
        ->willReturn(true);
        
        $this->assertEquals($subscriptionUserNote, $this->helper->getSubscriptionUserNotes());
    }
}
