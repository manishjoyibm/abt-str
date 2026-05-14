<?php

namespace Abbott\MyAccount\Test\Unit\Helper;

class DataTest extends \PHPUnit\Framework\TestCase
{

     public $contextMock;
     public $storemanagerMock;
     /**
      * @var (\Magento\Integration\Model\CustomerTokenService & \PHPUnit\Framework\MockObject\MockObject)
      */
     public $customertokenMock;
     /**
      * @var (\Magento\Framework\Stdlib\Cookie\CookieMetadataFactory & \PHPUnit\Framework\MockObject\MockObject)
      */
     public $cookiemetaMock;
     /**
      * @var (\Magento\Framework\Stdlib\Cookie\PhpCookieManager & \PHPUnit\Framework\MockObject\MockObject)
      */
     public $phpcookieMock;
     /**
      * @var (\Magento\Framework\Session\Config & \PHPUnit\Framework\MockObject\MockObject)
      */
     public $sessionconfigMock;
     /**
      * @var (\Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer & \PHPUnit\Framework\MockObject\MockObject)
      */
     public $emptycartMock;
     /**
      * @var (\Magento\Quote\Api\CartManagementInterface & \PHPUnit\Framework\MockObject\MockObject)
      */
     public $cartmanagementMock;
     /**
      * @var (\Magento\Quote\Model\QuoteIdMaskFactory & \PHPUnit\Framework\MockObject\MockObject)
      */
     public $quoteidfactoryMock;
     /**
      * @var (\Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask & \PHPUnit\Framework\MockObject\MockObject)
      */
     public $quoteidMock;
     /**
      * @var (\Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface & \PHPUnit\Framework\MockObject\MockObject)
      */
     public $quoteidmaskedinterfaceMock;
     /**
      * @var (\Magento\Framework\Json\Helper\Data & \PHPUnit\Framework\MockObject\MockObject)
      */
     public $jsonhelperMock;
     /**
      * @var (\Magento\Catalog\Api\ProductRepositoryInterface & \PHPUnit\Framework\MockObject\MockObject)
      */
     public $productRepoMock;
     /**
      * @var (\Magento\Integration\Model\Oauth\TokenFactory & \PHPUnit\Framework\MockObject\MockObject)
      */
     public $tokenfactMock;
     /**
      * @var (\Magento\Customer\Api\AddressMetadataInterface & \PHPUnit\Framework\MockObject\MockObject)
      */
     public $addressmetaMock;
     /**
      * @var (\Magento\Framework\Escaper & \PHPUnit\Framework\MockObject\MockObject)
      */
     public $escaperMock;
     /**
      * @var (\Magento\Store\Api\StoreRepositoryInterface & \PHPUnit\Framework\MockObject\MockObject)
      */
     public $storeRepoMock;
     public $customersessionMock;
     public $scopeConfig;
     public $scopeConfigMock;
     public $myaccounthelperdataMock;
     public $_website;
     public function setUp() : void
    {
        $this->contextMock  = $this->getMockBuilder(\Magento\Framework\App\Helper\Context::class)->disableOriginalConstructor()->getMock();

        $this->storemanagerMock  = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)->disableOriginalConstructor()->getMock();

        $this->customertokenMock  = $this->getMockBuilder(\Magento\Integration\Model\CustomerTokenService::class)->disableOriginalConstructor()->getMock();

        $this->cookiemetaMock  = $this->getMockBuilder(\Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class)->disableOriginalConstructor()->getMock();

        $this->phpcookieMock  = $this->getMockBuilder(\Magento\Framework\Stdlib\Cookie\PhpCookieManager::class)->disableOriginalConstructor()->getMock();

        $this->sessionconfigMock  = $this->getMockBuilder(\Magento\Framework\Session\Config::class)->disableOriginalConstructor()->getMock();

        $this->emptycartMock  = $this->getMockBuilder(\Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer::class)->disableOriginalConstructor()->getMock();

        $this->cartmanagementMock  = $this->getMockBuilder(\Magento\Quote\Api\CartManagementInterface::class)->disableOriginalConstructor()->getMock();

        $this->quoteidfactoryMock  = $this->getMockBuilder(\Magento\Quote\Model\QuoteIdMaskFactory::class)->disableOriginalConstructor()->getMock();

        $this->quoteidMock  = $this->getMockBuilder(\Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask::class)->disableOriginalConstructor()->getMock();

        $this->quoteidmaskedinterfaceMock  = $this->getMockBuilder(\Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface::class)->disableOriginalConstructor()->getMock();

        $this->jsonhelperMock  = $this->getMockBuilder(\Magento\Framework\Json\Helper\Data::class)->disableOriginalConstructor()->getMock();

        $this->productRepoMock  = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)->disableOriginalConstructor()->getMock();

        $this->tokenfactMock  = $this->getMockBuilder(\Magento\Integration\Model\Oauth\TokenFactory::class)->disableOriginalConstructor()->getMock();

        $this->addressmetaMock  = $this->getMockBuilder(\Magento\Customer\Api\AddressMetadataInterface::class)->disableOriginalConstructor()->getMock();

        $this->escaperMock  = $this->getMockBuilder(\Magento\Framework\Escaper::class)->disableOriginalConstructor()->getMock();

        $this->storeRepoMock  = $this->getMockBuilder(\Magento\Store\Api\StoreRepositoryInterface::class)->disableOriginalConstructor()->getMock();

        $this->customersessionMock  = $this->getMockBuilder(\Magento\Customer\Model\Session::class)->disableOriginalConstructor()->setMethods(['unsReturnsave','unsAddsave','unsEditshipingsave','unsEditsave','unsEditbillingsave','getAddsave','getEditsave','getEditbillingsave','getEditshipingsave','getReturnsave'])->getMock();

        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
             ->getMockForAbstractClass();

        $this->scopeConfigMock = $this->createPartialMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            ['getValue', 'isSetFlag']
        );
        $this->contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->myaccounthelperdataMock = new \Abbott\MyAccount\Helper\Data($this->contextMock, $this->storemanagerMock, $this->customertokenMock, $this->cookiemetaMock, $this->phpcookieMock, $this->sessionconfigMock, $this->emptycartMock, $this->cartmanagementMock, $this->quoteidfactoryMock, $this->quoteidMock, $this->quoteidmaskedinterfaceMock, $this->jsonhelperMock, $this->productRepoMock, $this->tokenfactMock, $this->addressmetaMock, $this->escaperMock, $this->storeRepoMock, $this->customersessionMock);

    }
    public function testgetConfigGoogleAnalyticsEnable() : void
    {
        $test['store_id'] = 1;
        $test['google_string'] = 'my_account/googleanalytics/ga_enabled';
        $test['expected_value'] = true;

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->with($test['google_string'], \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->testgetStoreId())
            ->willReturn(1);

        $this->assertEquals(null, $this->myaccounthelperdataMock->getConfigGoogleAnalyticsEnable());

    }

    public function testgetStoreId()
    {
        $test['store_id'] = 1;
        $this->_website = $this->getMockBuilder(\Magento\Store\Model\Store::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->_website->method('getId')->will($this->returnValue($test['store_id']));

        $this->storemanagerMock->method('getStore')->will($this->returnValue($this->_website));
    }

    public function testunsetGAreturnSession()
    {
        $this->customersessionMock->method('unsReturnsave')->will($this->returnValue(false));

        $this->assertEquals(false, $this->myaccounthelperdataMock->unsetGAreturnSession());

        $this->myaccounthelperdataMock->unsetGAreturnSession();

    }

    public function testunsetSession()
    {
        $this->customersessionMock->method('unsAddsave')->will($this->returnValue(false));

        $this->customersessionMock->method('unsEditshipingsave')->will($this->returnValue(false));

        $this->customersessionMock->method('unsEditbillingsave')->will($this->returnValue(false));

        $this->customersessionMock->method('unsEditsave')->will($this->returnValue(false));

        $this->myaccounthelperdataMock->unsetSession();


    }

    public function testgetGASession()
    {
        $session['add'] = 1;
        $session['edit'] = 1;
        $session['billingedit'] = 1;
        $session['shipingedit'] = 1;

        $this->customersessionMock->method('getAddsave')->will($this->returnValue(1));

        $this->customersessionMock->method('getEditsave')->will($this->returnValue(1));

        $this->customersessionMock->method('getEditbillingsave')->will($this->returnValue(1));

        $this->customersessionMock->method('getEditshipingsave')->will($this->returnValue(1));

        $this->assertEquals($session, $this->myaccounthelperdataMock->getGASession());
    }

    public function testgetGAreturnSession()
    {

        $this->customersessionMock->method('getReturnsave')->will($this->returnValue(1));

        $this->myaccounthelperdataMock->getGAreturnSession();
    }


    // public function testafterExecute() : void
    // {
    //     $this->myaccountHelperMock->expects($this->any())->method('getConfigGoogleAnalyticsEnable')->will($this->returnValue(true));

    //     $customerId = 1;

    //    $this->customer = $this->getMockForAbstractClass(\Magento\Customer\Api\Data\CustomerInterface::class);

    //     $this->customerRepoMock->expects($this->once())
    //         ->method('getById')
    //         ->willReturn($this->customer);

    //     $shippingAddressId = 1;
    //     $billingAddressId = 1;

    //     $this->customer->expects($this->once())
    //         ->method('getDefaultBilling')
    //         ->willReturn($billingAddressId);

    //     $this->customer->expects($this->once())
    //         ->method('getDefaultShipping')
    //         ->willReturn($shippingAddressId);

    //     $id = 1;

    //     $this->requestMock->expects($this->any())->method('getParam')->with('id')->willReturn($id);

    //     $this->assertEquals(null, $this->formPostMock->afterExecute());

    // }
    // public function testaftertwoExecute() : void
    // {
    //     $this->myaccountHelperMock->expects($this->any())->method('getConfigGoogleAnalyticsEnable')->will($this->returnValue(true));

    //     $customerId = 1;

    //    $this->customer = $this->getMockForAbstractClass(\Magento\Customer\Api\Data\CustomerInterface::class);

    //     $this->customerRepoMock->expects($this->once())
    //         ->method('getById')
    //         ->willReturn($this->customer);

    //     $shippingAddressId = 1;
    //     $billingAddressId = 1;

    //     $this->customer->expects($this->once())
    //         ->method('getDefaultBilling')
    //         ->willReturn($billingAddressId);

    //     $this->customer->expects($this->once())
    //         ->method('getDefaultShipping')
    //         ->willReturn($shippingAddressId);

    //     $id = null;

    //     $this->requestMock->expects($this->any())->method('getParam')->with('id')->willReturn($id);

    //     $this->assertEquals(null, $this->formPostMock->afterExecute());

    // }
    
}
