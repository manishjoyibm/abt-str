<?php

namespace Abbott\AwsLambda\Test\Unit\Observer;

class AfterAddressSaveObserverTest extends \PHPUnit\Framework\TestCase
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
    public $afterAddressSaveObserverMock;
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
        $this->afterAddressSaveObserverMock = $this->objectManager->getObject(
            \Abbott\AwsLambda\Observer\afterAddressSaveObserver::class,
            [
                'helper' => $this->helperMock,
                'log' => $this->logMock
            ]
        );
    }

    public function testExecute()
    {
        $addressId = 116355;
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
        $customer->expects($this->any())
            ->method('getDefaultBilling')
            ->willReturn(null);
        $customer->expects($this->any())
            ->method('getDefaultShipping')
            ->willReturn(null);
        $address = $this->getMockBuilder(\Magento\Customer\Model\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'getIsDefaultBilling',
                    'getIsDefaultShipping',
                    'setForceProcess',
                    'getIsPrimaryBilling',
                    'getIsPrimaryShipping',
                    'getCustomer',
                    'getForceProcess'
                ]
            )
            ->getMock();
        $address->expects($this->any())
            ->method('getId')
            ->willReturn($addressId);
        $address->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customer);
        $address->expects($this->any())
            ->method('getIsPrimaryBilling')
            ->willReturn(null);
        $address->expects($this->any())
            ->method('getIsDefaultBilling')
            ->willReturn($addressId);
        $address->expects($this->any())
            ->method('getIsPrimaryShipping')
            ->willReturn(null);
        $address->expects($this->any())
            ->method('getIsDefaultShipping')
            ->willReturn($addressId);

        $observer = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getCustomerAddress',
            ])
            ->getMock();
        $observer->expects($this->once())
            ->method('getCustomerAddress')
            ->willReturn($address);

        $this->afterAddressSaveObserverMock->execute($observer);
    }
}
