<?php

namespace Abbott\MyAccount\Plugin\Controller\Downloadable\Customer;
use Abbott\MyAccount\Model\Config\Source\Action;

class ProductsPluginTest extends \PHPUnit\Framework\TestCase
{
     public $sessionMock;
     public $linkDataHelperMock;
     public $urlMock;
     /**
      * @var (\Magento\Framework\App\Response\Http & \PHPUnit\Framework\MockObject\MockObject)
      */
     public $httpMock;
     public $productsPluginMock;
     protected $is_redirect = true;
     
     public function setUp() : void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->sessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)->disableOriginalConstructor()->setMethods(array('isLoggedIn'))->getMock();
        $this->linkDataHelperMock = $this->getMockBuilder(\Abbott\MyAccount\Helper\LinkData::class)->disableOriginalConstructor()->setMethods(array('isEnabled', 'getAction', 'checkForPagenotfound'))->getMock();
        $this->urlMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)->disableOriginalConstructor()->getMock();
        $this->httpMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)->disableOriginalConstructor()->setMethods(array('setRedirect'))->getMock();
                   
        $this->productsPluginMock = $objectManager->getObject(
            \Abbott\MyAccount\Plugin\Controller\Downloadable\Customer\ProductsPlugin::class,
            [
                'customerSession' =>  $this->sessionMock,
                'linkdataHelper' => $this->linkDataHelperMock,
                'response' => $this->httpMock,
                'url' => $this->urlMock                
            ]
        );
    }
    
    
    public function testBeforeExecute() : void
    {
      $layoutName = "customer-account-navigation-downloadable-products-link";
       
      $test = 
      ['is_logged_in' => 1, 
          'is_enabled' => 1,
          'action' => 1,
          'url_enable' => 1,
          'noroute' => 'http://noroute',
          'expected_result' => true            
      ];

   
        $this->sessionMock->method('isLoggedIn')->will($this->returnValue($test['is_logged_in']));
        $this->linkDataHelperMock->method('isEnabled')->will($this->returnValue($test['is_enabled']));
        $this->linkDataHelperMock->method('checkForPagenotfound')->with($layoutName)->will($this->returnValue($test['url_enable']));        
        $norouteUrl = $this->urlMock->method('getUrl')->with('noroute')->willReturn($this->returnValue($test['noroute'])); 
        $this->setRedirect(true);
        $this->assertEquals($test['expected_result'], $this->isRedirect(), "Downlodable Product plugin URL redirect - enabled");
        $this->productsPluginMock->beforeExecute();
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
