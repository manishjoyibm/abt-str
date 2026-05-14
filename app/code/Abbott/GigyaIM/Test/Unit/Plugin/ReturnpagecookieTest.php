<?php

namespace Abbott\GigyaIM\Test\Unit\Plugin;

class ReturnpagecookieTest extends \PHPUnit\Framework\TestCase
{

    public $helperMock;
    /**
     * @var (\Abbott\AwsLambda\Logger\Log & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $loggerMock;
    public $storeManagerMock;
    /**
     * @var (\Magento\Customer\Model\Session & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $customerSessionMock;
    /**
     * @var (\Abbott\MyAccount\Helper\Data & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $myaccounthelperMock;
    public $helper;
    public $returnBlock;
    public function setUp() : void
    {
        $this->helperMock  = $this->getMockBuilder(\Abbott\GigyaIM\Helper\Data::class)->disableOriginalConstructor()->setMethods(['setCookie', 'getCustomCookie'])->getMock();

        $this->loggerMock  = $this->getMockBuilder(\Abbott\AwsLambda\Logger\Log::class)->disableOriginalConstructor()->getMock();

        $this->storeManagerMock  = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)->disableOriginalConstructor()->getMock();

        $this->customerSessionMock  = $this->getMockBuilder(\Magento\Customer\Model\Session::class)->disableOriginalConstructor()->getMock();

        $this->myaccounthelperMock  = $this->getMockBuilder(\Abbott\MyAccount\Helper\Data::class)->disableOriginalConstructor()->getMock();

        $this->helper = new \Abbott\GigyaIM\Plugin\Returnpagecookie($this->loggerMock, $this->helperMock, $this->storeManagerMock, $this->customerSessionMock, $this->myaccounthelperMock);
    }
    public function testAfterSaveRma()
    {
        $test['abt_usr'] = '{"customer_id":"12","token":"123456","fname":"Kruti","cgroup":"1234567789","link_hide":{"returns":1},"magento_page":{"orders":1,"subscriptions":1}}';
       

        $storeMock = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);

        $storeMock->expects($this->once())
            ->method('getCode')
            ->willReturn('test');

        $this->storeManagerMock->expects($this->once())
        ->method('getStore')
        ->willReturn($storeMock);


        $this->helperMock->method('getCustomCookie')->will($this->returnValue($test['abt_usr']));
        $this->returnBlock = $this->createPartialMock(
            \Magento\Rma\Model\Rma::class,
            []
        );
        $this->assertEquals($this->returnBlock, $this->helper->afterSaveRma($this->returnBlock));
    }
}
