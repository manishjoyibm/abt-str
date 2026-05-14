<?php
namespace Abbott\Strongmoms\Test\Unit\Helper;

use Magento\Framework\View\LayoutFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
 
class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var (\Magento\Framework\App\RequestInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $requestinterfaceMock;
    /**
     * @var (\Magento\Customer\Api\AddressRepositoryInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $addressinterfaceMock;
    public $resourceMock;
    public $connectionMock;
    const XML_PATH_SSMUSER = 'aboott_message/ssm_user_notes/messagenote';
    const XML_PATH_SUBSCRIPTIONUSER = 'aboott_message/subscription_user_notes/subscriptionusermessage';
    /**
     * @var \Magento\Framework\App\Helper\Context\
     */
    protected $contextMock;
     /**
      * @var \Magento\Customer\Model\Session\
      */
    protected $customerSessionMock;
     /**
      * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory\
      */
    protected $orderCollectionFactoryMock;
     /**
      * @var \Magento\Store\Model\StoreManagerInterface\
      */
    protected $storeManagerMock;
    /**
     * @var \Magento\Store\Model\Store\
     */
    protected $_website;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface\
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface\
     */
    protected $scopeConfigMock;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface\
     */
    protected $orderCollectionFactoryInterface;
    /**
     * @var \Abbott\Strongmoms\Helper\Data\
     */
    protected $helper;
    const IS_SIMILAC_SSM = "similac-ssm";
    /**
     * @return void
     */
    protected function setUp()
    {
        $this->_website = $this->getMockBuilder(\Magento\Store\Model\Store::class)
        ->disableOriginalConstructor()
        ->getMock();
        
         $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
             ->getMockForAbstractClass();
       
        $this->scopeConfigMock = $this->createPartialMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            ['getValue', 'isSetFlag']
        );
        $contextMock = $this->createPartialMock(\Magento\Framework\App\Helper\Context::class, ['getScopeConfig']);

        $contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->orderCollectionFactoryInterface =
            $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface::class)
                ->disableOriginalConstructor()->setMethods(['create'])->getMock();

        $this->orderCollectionFactoryMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\CollectionFactory::class)->disableOriginalConstructor()->setMethods(['create','load','addFieldToSelect', 'addFieldToFilter', 'setOrder','getSize'])->getMock();

        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->requestinterfaceMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->addressinterfaceMock = $this->getMockBuilder(\Magento\Customer\Api\AddressRepositoryInterface::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
        ->disableOriginalConstructor()->setMethods(['select','from','where', 'fetchrow','getConnection'])
        ->getMock();

        $this->helper = $this->getMockBuilder(
            \Abbott\Strongmoms\Helper\Data::class
        )
        ->setConstructorArgs(
            [
            $contextMock,
            $this->customerSessionMock,
            $this->orderCollectionFactoryMock,
            $this->storeManagerMock,
            $this->requestinterfaceMock,
            $this->addressinterfaceMock,
            $this->resourceMock
            ]
        )->setMethods([
        'getStoreId'
        ])
        ->getMock();

        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config::class);
    }

    /**
     * @return void
     */
    public function testIsSSM()
    {
        $user_type = 'similac-ssm';
        $returnval = true;

        $customerMock = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)
        ->disableOriginalConstructor()
        ->getMock();
        
        $customerMock->expects($this->any())
        ->method('getData')
        ->willReturn($user_type);

        $this->customerSessionMock->expects($this->once())
        ->method('getCustomer')
        ->will($this->returnValue($customerMock));

        $this->assertEquals($returnval, $this->helper->isSSM());
    }
    /**
     * @return void
     */
    public function testIsSSMFalse()
    {
        $user_type = 'similac-ssmabc';
        $returnval = false;

        $customerMock = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)
        ->disableOriginalConstructor()
        ->getMock();
        
        $customerMock->expects($this->any())
        ->method('getData')
        ->willReturn($user_type);

        $this->customerSessionMock->expects($this->once())
        ->method('getCustomer')
        ->will($this->returnValue($customerMock));

        $this->assertEquals($returnval, $this->helper->isSSM());
    }

    /**
     * @return int
     */
    public function testGetSsmUserConfig()
    {
        
        $storeId = 4;
        $testMethod = new \ReflectionMethod(\Abbott\Strongmoms\Helper\Data::class, 'getStoreId');
        $testMethod->setAccessible(true);
        $this->_website->method('getId')->will($this->returnValue($storeId));
        $this->storeManagerMock->method('getStore')->will($this->returnValue($this->_website));

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->with(self::XML_PATH_SSMUSER, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn(1);
            
        $this->assertSame(null, $this->helper->getSsmUserConfig());
    }

    /**
     * @return int
     */
    public function testGetStoreId()
    {
        $storeId = 1;
        $testMethod = new \ReflectionMethod(\Abbott\Strongmoms\Helper\Data::class, 'getStoreId');
        $testMethod->setAccessible(true);
        $this->_website->method('getId')->will($this->returnValue($storeId));
        $this->storeManagerMock->method('getStore')->will($this->returnValue($this->_website));
      
        $this->assertEquals($storeId, $testMethod->invokeArgs($this->helper, [$storeId]), "Get the current store ID");
    }
    public function testGetSsmUserOrderCount()
    {
        $customer_id = 1;
        $size = 32;

        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customer->expects($this->any())->method('getId')->willReturn($customer_id);

        $this->customerSessionMock->expects($this->once())
        ->method('getCustomer')
        ->will($this->returnValue($customer));

        $this->orderCollectionFactoryMock->expects($this->at(0))->method('create')->will($this->returnValue($this->orderCollectionFactoryMock));

        $this->orderCollectionFactoryMock->expects($this->at(1))
            ->method('addFieldToSelect')
            ->with($this->equalTo('entity_id'))
            ->will($this->returnValue($this->orderCollectionFactoryMock));

        $this->orderCollectionFactoryMock->expects($this->at(2))
            ->method('addFieldToFilter')
            ->with('customer_id', $customer_id)
            ->will($this->returnValue($this->orderCollectionFactoryMock));

            $this->orderCollectionFactoryMock->expects($this->at(3))
            ->method('setOrder')
            ->will($this->returnValue($this->orderCollectionFactoryMock));

            $this->orderCollectionFactoryMock->expects($this->at(4))
            ->method('getSize')
            ->will($this->returnValue($size));
           $this->assertEquals($size, $this->helper->getSsmUserOrderCount());
    }

    /**
     * @return int
     */
    public function testGetsubscriptionUserConfig()
    {
        
        $storeId = 4;
        $testMethod = new \ReflectionMethod(\Abbott\Strongmoms\Helper\Data::class, 'getStoreId');
        $testMethod->setAccessible(true);
        $this->_website->method('getId')->will($this->returnValue($storeId));
        $this->storeManagerMock->method('getStore')->will($this->returnValue($this->_website));

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->with(self::XML_PATH_SSMUSER, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn(1);
            
        $this->assertSame(null, $this->helper->getsubscriptionUserConfig());
    }

    public function testGetuserSubscription()
    {
        $customer_id = 1;
        $size = 32;

        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customer->expects($this->any())->method('getId')->willReturn($customer_id);

        $this->customerSessionMock->expects($this->once())
        ->method('getCustomer')
        ->will($this->returnValue($customer));

        $this->connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);


        $this->resourceMock->expects($this->at(0))
        ->method('getConnection')
        ->will($this->returnValue($this->connectionMock));

        $this->connectionMock->expects($this->at(1))
        ->method('select')
        ->will($this->returnValue($this->resourceMock));

        $this->connectionMock->expects($this->at(2))
        ->method('from')->with(['sarp2_profile'=>'aw_sarp2_profile'])
        ->will($this->returnValue($this->connectionMock));
        
        $this->connectionMock->expects($this->at(3))
        ->method('where')->with('sarp2_profile.customer_id=1')
        ->will($this->returnValue($this->connectionMock));
    
        $this->connectionMock->expects($this->at(4))
        ->method('fetchRow')->with($this->connectionMock)
        ->will($this->returnValue(array()));

           $this->assertEquals(true, $this->helper->getuserSubscription());
    }
    
}
