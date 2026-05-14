<?php

namespace Abbott\AwsLambda\Test\Unit\Observer;

class CustomerSaveAfterObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    public $objectManager;
    /**
     * @var (\Abbott\AwsLambda\Logger\Log & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $logMock;
    public $helperMock;
    /**
     * @var (\Magento\Framework\Event & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $event;
    /**
     * @var (\Magento\Framework\Event\Observer & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $eventObserver;
    /**
     * @var (\Magento\Framework\App\Config\ScopeConfigInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $scopeConfig;
    /**
     * @var (\Magento\Customer\Model\Session & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $customerSessionMock;
    public $customerSaveAfterObserverMock;
    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->logMock = $this->createMock(\Abbott\AwsLambda\Logger\Log::class);
        $this->helperMock = $this->createMock(\Abbott\AwsLambda\Helper\Data::class);
        
        $this->event = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerAddress'])
            ->getMock();

        $this->eventObserver = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSaveAfterObserverMock = $this->objectManager->getObject(
            \Abbott\AwsLambda\Observer\CustomerSaveAfterObserver::class,
            [
                'helper' => $this->helperMock,
                'log' => $this->logMock
            ]
        );
    }

    public function testExecute()
    {
        $this->helperMock->expects($this->once())->method('enabled')->willReturn(true);
        $this->helperMock->expects($this->once())->method('getPostUrl')->willReturn(
            "https://dev.similac.com/api/private/profile/update-profile-info"
        );
        $this->helperMock->expects($this->once())->method('postData')->with($this->anything())->willReturn("success");
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customer = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultBilling', 'getStore', 'getDefaultShipping', 'getGroupId'])
            ->getMock();
        $customer->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
        $observer = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomer',
            ])
            ->getMock();
        $observer->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customer);

        $this->customerSaveAfterObserverMock->execute($observer);
    }
}
