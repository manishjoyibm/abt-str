<?php

namespace Abbott\ProgressiveDiscount\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Abbott\ProgressiveDiscount\Observer\RestrictAddToCart;
class RestrictAddToCartTest  extends \PHPUnit\Framework\TestCase
{
/**
 * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
 */
public $objectManager;
public $customerSessionMock;
public $helperMock;
/**
 * @var (\Magento\Framework\Message\ManagerInterface & \PHPUnit\Framework\MockObject\MockObject)
 */
public $messageManagerMock;
/**
 * @var (\Magento\Framework\App\RequestInterface & \PHPUnit\Framework\MockObject\MockObject)
 */
public $requestMock;
/**
 * @var (\Magento\Framework\App\Response\Http & \PHPUnit\Framework\MockObject\MockObject)
 */
public $httpMock;
/**
 * @var (\Magento\Framework\UrlInterface & \PHPUnit\Framework\MockObject\MockObject)
 */
public $urlMock;
/**
 * @var (\Magento\Framework\Event & \PHPUnit\Framework\MockObject\MockObject)
 */
public $eventMock;
public $restrictPluginObserver;
public $is_redirect;
private $observerMock;

    public function setUp() {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->observerMock = $this->createMock(Observer::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->helperMock = $this->createMock(\Abbott\ProgressiveDiscount\Helper\Data::class);
        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->httpMock = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->urlMock = $this->createMock(\Magento\Framework\UrlInterface::class);


		$this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSource','getQuoteItem'])
            ->getMock();

        $this->restrictPluginObserver = new RestrictAddToCart(
           $this->customerSessionMock,
             $this->helperMock,
            $this->messageManagerMock,
            $this->requestMock,
             $this->httpMock,
           $this->urlMock

        );
      //  $this->subject = $objectManager->getObject(\Abbott\ProgressiveDiscount\Observer\RestrictAddToCart::class);
        //$this->observerMock = $this->createPartialMock(\Magento\Framework\Event\Observer::class, ['getEvent']);
    }

    public function testExecute() {
        $test = [
            'is_logged_in' => 1,
            'customer_id' => 1234,

            'active' => 'active',
            'checkout_restriction' => 1,
            'expected_result' => true,
            'message' => 'checkout restriction message',
            'url' => 'cart/checkout'
        ];
        
        $redirect = function() {
            $this->setRedirect(true);
        };
        $this->customerSessionMock->method('isLoggedIn')->willReturn($this->returnValue($test['is_logged_in']));
        $this->helperMock->method('getIsProgressiveCheckoutRestricted')->will($this->returnValue($test['checkout_restriction']));
        $this->customerSessionMock->method('getCustomer')->will($this->returnSelf());
        $this->customerSessionMock->method('getId')->will($this->returnValue($test['customer_id']));
               
        $quoteItemMock = $this->createMock(
            \Magento\Quote\Model\Quote\Item::class
           );
           $quoteItemMock->expects($this->any())->method('getData')->will($this->returnSelf());

        $sourceMock = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getQuoteItem']);
        $sourceMock->expects($this->once())->method('getQuoteItem')->will($this->returnValue($quoteItemMock));

        $eventMock = $this->createPartialMock(\Magento\Framework\Event::class, [ 'getSource']);
        $quoteItems = $eventMock->expects($this->once())->method('getSource')->will($this->returnValue($sourceMock));

        
        $this->helperMock->method('isSubscriptionActive')->with($test['customer_id'])->will($this->returnValue($test['expected_result']));
        $this->helperMock->method('getActiveSubscriptionCheckoutMessage')->will($this->returnValue($test['message']));
        //$this->helperMock->method('checkCartItems')->with($quoteItems, 'active')->will($this->returnValue(true));
        $this->observerMock->method('getEvent')->willReturn($eventMock);
        
        
      
        //$eventMock->expects($this->once())->method('getQuoteItems')->will($this->returnValue($quoteMock));
       
        $this->observerMock->expects($this->exactly(1))->method('getEvent')->will($this->returnValue($eventMock));

        $this->assertNull($this->restrictPluginObserver->execute($this->observerMock));	
       
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
