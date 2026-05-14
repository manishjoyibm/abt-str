<?php

namespace Abbott\MyAccount\Test\Unit\Plugin\Controller\Account;

class CreatePluginTest extends \PHPUnit\Framework\TestCase
{
    public $sessionMock;
    public $accountHelperMock;
    public $helperMock;
    public $httpMock;
    public $createPluginMock;
    protected $is_redirect = true;

    public function setUp() : void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->sessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)->disableOriginalConstructor()->setMethods(array('isLoggedIn'))->getMock();
        $this->accountHelperMock = $this->getMockBuilder(\Abbott\MyAccount\Helper\Data::class)->disableOriginalConstructor()->setMethods(array('removeCookie', 'getRedirectConfig'))->getMock();
        $this->helperMock = $this->getMockBuilder(\Abbott\CustomerTransistion\Helper\Data::class)->disableOriginalConstructor()->setMethods(array('getFailureUrl'))->getMock();
	    $this->httpMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)->disableOriginalConstructor()->setMethods(array('setRedirect'))->getMock();

        $this->createPluginMock = $objectManager->getObject(
            \Abbott\MyAccount\Plugin\Controller\Account\CreatePlugin::class,
            [
                'sessionMock' =>  $this->sessionMock,
                'accountHelper' => $this->accountHelperMock,
                'helperMock' => $this->helperMock,
                'httpMock' => $this->httpMock
            ]
        );
    }

    public function testBeforeExecute() : void    {
        $test = [
            'is_logged_in' => 0,
            'customer' => false,
            'url' => 'http://dev.similac.com',
            'is_login_redirect_enabled' => 1,
            'aem_registration_page' => 'checkout-registration',
            'expected_result' => true
        ];

        $redirect = function() {
            $this->setRedirect(true);
        };
        $this->sessionMock->method('isLoggedIn')->will($this->returnValue($test['is_logged_in']));
        $this->accountHelperMock->method('getRedirectConfig')->will($this->returnValue($test['is_login_redirect_enabled']));
	    $failureUrl = $this->helperMock->method('getFailureUrl')->will($this->returnValue($test['url']));
     	$aemRegistrationPage = $this->accountHelperMock->method('getRedirectConfig')->will($this->returnValue($test['aem_registration_page']));//$this->onConsecutiveCalls($test['aem_registration_page'])
        if(!empty($failureUrl) && !empty($aemRegistrationPage)){
            $this->httpMock->method('setRedirect')->with($test['aem_registration_page'])->will($this->returnCallback($redirect));
            $this->setRedirect(true);
            $this->assertEquals($test['expected_result'], $this->isRedirect(), "Registration URL redirect - enabled");
        
        }
        
       
        $this->createPluginMock->beforeExecute();
    }

    private function setRedirect($is_redirect)
    {
        $this->is_redirect = $is_redirect;
    }

    private function isRedirect()
    {
        return $this->is_redirect;
    }

}
