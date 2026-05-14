<?php

namespace Abbott\MyAccount\Test\Unit\Plugin\Controller\Account;

class IndexPluginTest extends \PHPUnit\Framework\TestCase
{
     /**
      * @var (\Magento\Customer\Model\Session & \PHPUnit\Framework\MockObject\MockObject)
      */
     public $sessionMock;
     public $customerSessionMock;
     public $linkDataHelperMock;
     public $urlMock;
     public $httpMock;
     public $indexPluginMock;
     protected $is_redirect = true;
     
     public function setUp() : void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->sessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)->disableOriginalConstructor()->setMethods(array('isLoggedIn'))->getMock();
        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['isLoggedIn','getCustomer'])
            ->getMock();
        $this->linkDataHelperMock = $this->getMockBuilder(\Abbott\MyAccount\Helper\LinkData::class)->disableOriginalConstructor()->setMethods(array('isEnabled', 'checkForPagenotfound'))->getMock();
         $this->urlMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)->disableOriginalConstructor()->getMock();
         $this->httpMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)->disableOriginalConstructor()->setMethods(array('setRedirect'))->getMock();
         
            $this->indexPluginMock = $objectManager->getObject(
                \Abbott\MyAccount\Plugin\Controller\Account\IndexPlugin::class,
                [
                    'customerSession' =>  $this->customerSessionMock,
                    'linkdataHelper' => $this->linkDataHelperMock,
                    'url' => $this->urlMock,
                    'response' => $this->httpMock                   
                ]
            );

    }
    
    
    public function testBeforeExecute() : void
    {
        $layoutName = "customer-account-navigation-account-link";
       
        $test = 
                ['is_logged_in' => 1, 
                    'is_enabled' => 1,
                    'is_redirect' => 1,
                    'action' => 1,
                    'section' => true,
                    'noroute' => 'http://norouturl',
                    'expected_result' => true             
                ];
        
          $redirect = function() {
            $this->setRedirect(true);
        };
        // $this->sessionMock->method('isLoggedIn')->willReturn(1);
         $this->customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->willReturn(1);
         $this->linkDataHelperMock->expects($this->any())->method('isEnabled')->willReturn($test['is_enabled']);
         $this->linkDataHelperMock->expects($this->any())->method('checkForPagenotfound')->with($layoutName)->will($this->returnValue($test['is_redirect']));        
         $this->urlMock->method('getUrl')->with('noroute')->will($this->returnValue($test['noroute']));
         $this->httpMock->method('setRedirect')->with($test['noroute'])->will($this->returnCallback($redirect));
         $this->setRedirect(true);
         $this->assertEquals($test['expected_result'], $this->isRedirect(), "Customer Index URL redirect - enabled");
         $this->indexPluginMock->beforeExecute();
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
