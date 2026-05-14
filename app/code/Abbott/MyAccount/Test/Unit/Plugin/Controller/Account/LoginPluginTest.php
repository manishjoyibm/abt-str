<?php

namespace Abbott\MyAccount\Test\Unit\Plugin\Controller\Account;

class LoginPluginTest extends \PHPUnit\Framework\TestCase
{
    public $sessionMock;
    public $accountHelperMock;
    public $cookieManagerMock;
    /**
     * @var (\Abbott\CustomerTransistion\Helper\Data & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $helperMock;
    public $httpMock;
    public $loginPluginMock;
    protected $is_redirect = false;

    public function setUp() : void
    {
        $this->sessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)->disableOriginalConstructor()->setMethods(array('isLoggedIn'))->getMock();
        $this->accountHelperMock = $this->getMockBuilder(\Abbott\MyAccount\Helper\Data::class)->disableOriginalConstructor()->setMethods(array('removeCookie', 'getRedirectConfig'))->getMock();
        $this->cookieManagerMock = $this->createMock(\Magento\Framework\Stdlib\CookieManagerInterface::class);
        $this->helperMock = $this->getMockBuilder(\Abbott\CustomerTransistion\Helper\Data::class)->disableOriginalConstructor()->setMethods(array('getFailureUrl'))->getMock();
		$this->httpMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)->disableOriginalConstructor()->setMethods(array('setRedirect'))->getMock();

		$this->loginPluginMock = $this->getMockBuilder(
			\Abbott\MyAccount\Plugin\Controller\Account\LoginPlugin::class)
			->setConstructorArgs(
				[
					$this->sessionMock,
					$this->accountHelperMock,
					$this->cookieManagerMock,
					$this->helperMock,
					$this->httpMock
				]
			)
			->getMock();
    }

    public function testBeforeExecuteCase1() : void
    {
        $testMethod = new \ReflectionMethod(\Abbott\MyAccount\Plugin\Controller\Account\LoginPlugin::class, 'beforeExecute');
        $testMethod->setAccessible(true);

        $subject = $this->getMockBuilder(\Magento\Customer\Controller\Account\Login::class)->disableOriginalConstructor()->getMock();

        $test = [
            'is_logged_in' => 0,
            'customer' => true,
            'aem_failuare_url' => 1,
			'aem_login_page' => 'checkout-login',
            'expected_result' => false
        ];

        $redirect = function() {
            $this->setRedirect(true);
        };
        $this->sessionMock->method('isLoggedIn')->will($this->returnValue($test['is_logged_in']));
        $this->cookieManagerMock->method('getCookie')->will($this->returnValue($test['customer']));
		$this->accountHelperMock->method('getRedirectConfig')->will($this->onConsecutiveCalls($test['aem_failuare_url'], $test['aem_login_page']));
		$this->httpMock->method('setRedirect')->with($test['aem_login_page'])->will($this->returnCallback($redirect));

        $this->setRedirect(false);
		$testMethod->invokeArgs($this->loginPluginMock, [$subject]);

        $this->assertEquals($test['expected_result'], $this->isRedirect(), "Login URL redirect - enabled");
    }

    public function testBeforeExecuteCase2() : void
    {
        $testMethod = new \ReflectionMethod(\Abbott\MyAccount\Plugin\Controller\Account\LoginPlugin::class, 'beforeExecute');
        $testMethod->setAccessible(true);

        $subject = $this->getMockBuilder(\Magento\Customer\Controller\Account\Login::class)->disableOriginalConstructor()->getMock();

        $test = [
            'is_logged_in' => 0,
            'customer' => true,
            'aem_failuare_url' => 0,
			'aem_login_page' => 'checkout-login',
            'expected_result' => false
        ];

        $redirect = function() {
            $this->setRedirect(true);
        };
        $this->sessionMock->method('isLoggedIn')->will($this->returnValue($test['is_logged_in']));
        $this->cookieManagerMock->method('getCookie')->will($this->returnValue($test['customer']));
		$this->accountHelperMock->method('getRedirectConfig')->will($this->onConsecutiveCalls($test['aem_failuare_url'], $test['aem_login_page']));
		$this->httpMock->method('setRedirect')->with($test['aem_login_page'])->will($this->returnCallback($redirect));

        $this->setRedirect(false);
        $testMethod->invokeArgs($this->loginPluginMock, [$subject]);

        $this->assertEquals($test['expected_result'], $this->isRedirect(), "Login URL redirect - disabled");
    }

    public function testBeforeExecuteCase3() : void
    {
        $testMethod = new \ReflectionMethod(\Abbott\MyAccount\Plugin\Controller\Account\LoginPlugin::class, 'beforeExecute');
        $testMethod->setAccessible(true);

        $subject = $this->getMockBuilder(\Magento\Customer\Controller\Account\Login::class)->disableOriginalConstructor()->getMock();

        $test = [
            'is_logged_in' => 1,
            'customer' => true,
            'aem_failuare_url' => 0,
			'aem_login_page' => 'checkout-login',
            'expected_result' => false
        ];

        $redirect = function() {
            $this->setRedirect(true);
        };
        $this->sessionMock->method('isLoggedIn')->will($this->returnValue($test['is_logged_in']));
        $this->cookieManagerMock->method('getCookie')->will($this->returnValue($test['customer']));
        $this->accountHelperMock->method('getRedirectConfig')->will($this->onConsecutiveCalls($test['aem_failuare_url'], $test['aem_login_page']));
		$this->httpMock->method('setRedirect')->with($test['aem_login_page'])->will($this->returnCallback($redirect));

        $this->setRedirect(false);
        $testMethod->invokeArgs($this->loginPluginMock, [$subject]);

        $this->assertEquals($test['expected_result'], $this->isRedirect(), "Login URL redirect - disabled");
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
