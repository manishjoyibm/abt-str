<?php

namespace Abbott\MyAccount\Test\Unit;

class FormPostPluginTest extends \PHPUnit\Framework\TestCase
{
     
     public $requestMock;
     /**
      * @var (\Magento\Framework\App\ViewInterface & \PHPUnit\Framework\MockObject\MockObject)
      */
     public $viewMock;
     /**
      * @var (\Magento\Customer\Model\Session & \PHPUnit\Framework\MockObject\MockObject)
      */
     public $sessionMock;
     public $customerRepoMock;
     public $myaccountHelperMock;
     public $formPostMock;
     public $customer;
     public function setUp() : void
    {
        $this->requestMock  = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)->disableOriginalConstructor()->getMock();

        $this->viewMock  = $this->getMockBuilder(\Magento\Framework\App\ViewInterface::class)->disableOriginalConstructor()->getMock();

        $this->sessionMock  = $this->getMockBuilder(\Magento\Customer\Model\Session::class)->disableOriginalConstructor()->getMock();

        $this->customerRepoMock  = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)->disableOriginalConstructor()->getMock();

        $this->myaccountHelperMock  = $this->getMockBuilder(\Abbott\MyAccount\Helper\Data::class)->disableOriginalConstructor()->setMethods(['getConfigGoogleAnalyticsEnable'])->getMock();

        $this->formPostMock = new \Abbott\MyAccount\Plugin\Controller\Address\FormPostPlugin($this->requestMock, $this->viewMock, $this->sessionMock, $this->customerRepoMock, $this->myaccountHelperMock);

    }
    
    public function testafterExecute() : void
    {
        $this->myaccountHelperMock->expects($this->any())->method('getConfigGoogleAnalyticsEnable')->will($this->returnValue(true));

        $customerId = 1;

       $this->customer = $this->getMockForAbstractClass(\Magento\Customer\Api\Data\CustomerInterface::class);

        $this->customerRepoMock->expects($this->once())
            ->method('getById')
            ->willReturn($this->customer);
        
        $shippingAddressId = 1;
        $billingAddressId = 1;

        $this->customer->expects($this->once())
            ->method('getDefaultBilling')
            ->willReturn($billingAddressId);

        $this->customer->expects($this->once())
            ->method('getDefaultShipping')
            ->willReturn($shippingAddressId);

        $id = 1;

        $this->requestMock->expects($this->any())->method('getParam')->with('id')->willReturn($id);

        $this->assertEquals(null, $this->formPostMock->afterExecute());

    }
    public function testaftertwoExecute() : void
    {
        $this->myaccountHelperMock->expects($this->any())->method('getConfigGoogleAnalyticsEnable')->will($this->returnValue(true));

        $customerId = 1;

       $this->customer = $this->getMockForAbstractClass(\Magento\Customer\Api\Data\CustomerInterface::class);

        $this->customerRepoMock->expects($this->once())
            ->method('getById')
            ->willReturn($this->customer);
        
        $shippingAddressId = 1;
        $billingAddressId = 1;

        $this->customer->expects($this->once())
            ->method('getDefaultBilling')
            ->willReturn($billingAddressId);

        $this->customer->expects($this->once())
            ->method('getDefaultShipping')
            ->willReturn($shippingAddressId);

        $id = null;

        $this->requestMock->expects($this->any())->method('getParam')->with('id')->willReturn($id);

        $this->assertEquals(null, $this->formPostMock->afterExecute());

    }
    
}
