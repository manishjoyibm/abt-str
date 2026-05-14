<?php

namespace Abbott\ProgressiveDiscount\Test\Unit\Helper;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status as ProfileStatus;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Grid\Collection as SubscriptionProfileGridCollection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataTest extends \PHPUnit\Framework\TestCase
{
    public $_website;
    public $storeMock;
    public $profileMock;
    public $scopeConfig;
    /**
     * @var (\Magento\Framework\App\Helper\Context & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $contextMock;
    public $planRepoMock;
    /**
     * @var (\Aheadworks\Sarp2\Model\Profile & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $profile;
    /**
     * @var (\Magento\Quote\Model\Quote\Item & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $itemMock;
    /**
     * @var (\Abbott\AwsLambda\Logger\Log & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $awsLogMock;
    /**
     * @var (\Aheadworks\Sarp2\Model\ProfileFactory & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $profileFactoryMock;
    public $helperMock;
    /**
     * @var (\Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $collectionFactoryMock;
    /**
     * @var (\Aheadworks\Sarp2\Model\ResourceModel\Profile & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $resourceMock;
    public $storeManagerMock;
    /**
     * @var (\Magento\Framework\App\Config\ScopeConfigInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $scopeConfigMock;
    const PROGRESSIVE_CHECKOUT_RESTRICTED = 'progressive_subscription_settings/progressive_subscription/restrict_cart_checkout';
    
    const PROGRESSIVE_CHECKOUT_SUBSCRIPTION_ACTIVE_MESSAGE = 'aboott_message/progressive_subscription/message_for_active_subscription';
    
    const PROGRESSIVE_CHECKOUT_MULTIPLE_PRODUCT_MESSAGE = 'aboott_message/progressive_subscription/message_for_multiple_progressive_product_in_cart';
    
    
    public function setUp() : void
    {
        $this->_website = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->profileMock = $this->getMockBuilder(\Aheadworks\Sarp2\Model\ProfileFactory::class)->disableOriginalConstructor()->setMethods(['getIsProgressive'])->getMock();
        $this->scopeConfig = $this->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')->setMethods(['isSetFlag', 'getValue'])->getMock();
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\App\Helper\Context::class)->disableOriginalConstructor()->getMock();
        $this->planRepoMock = $this->createMock(\Aheadworks\Sarp2\Api\PlanRepositoryInterface::class);
        $this->profile = $this->createMock(\Aheadworks\Sarp2\Model\Profile::class);
        $this->itemMock = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $this->awsLogMock = $this->createMock(\Abbott\AwsLambda\Logger\Log::class);
        $this->profileFactoryMock = $this->getMockBuilder(
            \Aheadworks\Sarp2\Model\ProfileFactory::class
        )
            ->setMethods(['create','getCollection','addFieldToFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperMock = $this->getMockBuilder(
            \Abbott\ProgressiveDiscount\Helper\Data::class
        )
            ->setConstructorArgs(
                [
                                    $this->storeMock,
                                    $this->profileFactoryMock,
                                    $this->scopeConfig,
                                    $this->planRepoMock,
                                    $this->awsLogMock,
                                    $this->contextMock
                ]
            )->setMethods([
                            'getSystemConfigValue',
                            'getStoreId'
            ])
            ->getMock();
        
        $this->collectionFactoryMock = $this->createMock(\Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory::class);
        $this->resourceMock = $this->createMock(\Aheadworks\Sarp2\Model\ResourceModel\Profile::class);
    }
    
    public function testGetStoreId()
    {
        $storeId = 1;
        $testMethod = new \ReflectionMethod(\Abbott\ProgressiveDiscount\Helper\Data::class, 'getStoreId');
        $testMethod->setAccessible(true);
        $this->_website->method('getId')->will($this->returnValue($storeId));
        $this->storeMock->method('getStore')->will($this->returnValue($this->_website));
      
        $this->assertEquals($storeId, $testMethod->invokeArgs($this->helperMock, [$storeId]), "Get the current store ID");
    }
    
   
    
    public function testGetIsProgressiveCheckoutRestricted()
    {
        $testMethod = new \ReflectionMethod(\Abbott\ProgressiveDiscount\Helper\Data::class, 'getIsProgressiveCheckoutRestricted');
        $testMethod->setAccessible(true);
        $value = 'test message2';
        $reslt =  $this->helperMock->expects($this->any())->method('getSystemConfigValue')
                ->with(self::PROGRESSIVE_CHECKOUT_RESTRICTED)
                ->will($this->returnValue($value));
        $this->assertEquals($value, $testMethod->invokeArgs($this->helperMock, [self::PROGRESSIVE_CHECKOUT_RESTRICTED]), "Get the Checkout message");
    }
     
    public function testGetActiveSubscriptionCheckoutMessage()
    {
        $testMethod = new \ReflectionMethod(\Abbott\ProgressiveDiscount\Helper\Data::class, 'getActiveSubscriptionCheckoutMessage');
        $testMethod->setAccessible(true);
        $value = 'test message2';
        $reslt =  $this->helperMock->expects($this->any())->method('getSystemConfigValue')
                ->with(self::PROGRESSIVE_CHECKOUT_SUBSCRIPTION_ACTIVE_MESSAGE)
                ->will($this->returnValue($value));
        $this->assertEquals($value, $testMethod->invokeArgs($this->helperMock, [self::PROGRESSIVE_CHECKOUT_SUBSCRIPTION_ACTIVE_MESSAGE]), "Subscription checkout Message");
    }
     
    public function testGetProductSubscriptionCheckoutMessage()
    {
        $testMethod = new \ReflectionMethod(\Abbott\ProgressiveDiscount\Helper\Data::class, 'getProductSubscriptionCheckoutMessage');
        $testMethod->setAccessible(true);
        $value = 'test message3';
        $reslt =  $this->helperMock->expects($this->any())->method('getSystemConfigValue')
                ->with(self::PROGRESSIVE_CHECKOUT_MULTIPLE_PRODUCT_MESSAGE)
                ->will($this->returnValue($value));
        $this->assertEquals($value, $testMethod->invokeArgs($this->helperMock, [self::PROGRESSIVE_CHECKOUT_MULTIPLE_PRODUCT_MESSAGE]), "Product Subscription Message");
    }
    
    public function testGetSystemConfigValue()
    {
        $storeId = 4;
 
        $testMethod = new \ReflectionMethod(\Abbott\ProgressiveDiscount\Helper\Data::class, 'getStoreId');
        $testMethod->setAccessible(true);
        
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->_website->method('getId')->will($this->returnValue($storeId));
       
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
        ->getMockForAbstractClass();
        
        $this->scopeConfigMock = $this->createPartialMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            ['getValue', 'isSetFlag']
        );
        
        $this->storeManagerMock->method('getStore')->will($this->returnValue($this->_website));
        
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->with(self::PROGRESSIVE_CHECKOUT_MULTIPLE_PRODUCT_MESSAGE, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn(1);
                
        $this->assertSame(null, $this->helperMock->getSystemConfigValue(self::PROGRESSIVE_CHECKOUT_MULTIPLE_PRODUCT_MESSAGE));
    }
   
    public function testGetIsProgressive()
    {
        $planid = true;
        $this->planRepoMock->expects($this->any())->method('get')->willReturn($this->profileMock);
        $result = $this->profileMock->expects($this->any())->method('getIsProgressive')->willReturn($this->profileMock);

        $this->assertEquals($this->profileMock, $this->helperMock->getIsProgressive($planid));
    }
   
    public function testGetIsProgressiveNot()
    {
        $planid = false;
        $this->assertEquals(false, $this->helperMock->getIsProgressive($planid));
    }
    public function testIsSubscriptionNotActive()
    {
        $result = false;
       
        $customerId = 0;
        $profileId = 1;
        $this->assertEquals($result, $this->helperMock->isSubscriptionActive($customerId));
    }
   
    public function testIsSubscriptionActive()
    {
        $result = false;

        $customerId = 1;
        $profileId = 1;

 //       $this->profileCollectionMock = $this->createMock(\Aheadworks\Sarp2\Model\ResourceModel\Profile\Collection::class,['addFieldToFilter','getSelect']);
 //       $this->profileFactoryMock->expects($this->any())->method('getCollection')->willReturn($this->profileCollectionMock);
        //$this->profileFactoryMock->create()->getCollection();
        //$this->assertEquals($result, $this->helperMock->isSubscriptionActive($customerId));
    }
}
