<?php
namespace Abbott\Checkout\Test\Unit\Controller\Customer;

class UpdateTest extends \PHPUnit\Framework\TestCase
{
    public $customerSession;
    public $jsonFactoryMock;
    public $resultJsonMock;
    /**
     * @var (\Abbott\AwsLambda\Logger\Log & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $logMock;
    public $customerFactoryMock;
    public $customerMock;
    public $customerRepository;
    public $object;
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $className = \Abbott\Checkout\Controller\Customer\Update::class;
        $arguments = $objectManager->getConstructArguments($className);

        $contextMock = $this->createMock(\Magento\Framework\App\Action\Context::class);
        $arguments['context'] = $contextMock;

        $this->customerSession = $this->createMock(\Magento\Customer\Model\Session::class);
        $arguments['customerSession'] = $this->customerSession;

        $this->jsonFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])->getMock();

        $this->resultJsonMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logMock = $this->createMock(\Abbott\AwsLambda\Logger\Log::class);
        $arguments['resultJsonFactory'] = $this->jsonFactoryMock;
        $arguments['log'] = $this->logMock;

        $this->customerFactoryMock = $this->createPartialMock(
            \Magento\Customer\Model\ResourceModel\CustomerFactory::class,
            ['create', 'saveAttribute']
        );
        $this->customerMock = $this->createMock(\Magento\Customer\Model\Customer::class);
        $arguments['customerModel'] = $this->customerMock;
        $this->customerFactoryMock->method('create')->willReturn($this->customerMock);
        $arguments['customerFactory'] = $this->customerFactoryMock;

        $this->customerRepository = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $arguments['customerRepo'] = $this->customerRepository;

        $this->object = $objectManager->getObject(
            $className,
            $arguments
        );

        [
            'customerSession' => $this->customerSession,
            'customerRepo' => $this->customerRepository,
            'resultJsonFactory' => $this->jsonFactoryMock,
            'customerModel' => $this->customerMock,
            'customerFactory' => $this->customerFactoryMock
        ];
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $data = ['success' => false];
        $data1 = ['success' => true];
        $customerId = 231;
        $userType = 'similac-ssm';
        $this->jsonFactoryMock->expects($this->at(0))
            ->method('create')
            ->willReturn($this->resultJsonMock);
        $this->resultJsonMock->expects($this->at(0))
            ->method('setData')
            ->with($data)
            ->willReturnSelf();
        $this->customerSession->method('isLoggedIn')->will($this->returnValue(1));
        $this->customerSession->method('getCustomer')->willReturn($this->customerMock);
        $this->customerMock->method('getId')->will($this->returnValue($customerId));
        $customerData = $this->getMockBuilde(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMockForAbstractClass();

        $attributeMock = $this->getMockBuilder(\Magento\Framework\Api\AbstractExtensibleObject::class)
                                ->setMethods(['getValue'])
                                ->disableOriginalConstructor()
                                ->getMockForAbstractClass();

        $attributeMock
            ->method('getValue')
            ->willReturn($userType);

        $customerData->expects($this->at(0))
            ->method('getCustomAttribute')
            ->with('user_type')
            ->willReturn($attributeMock);
        $customerData->expects($this->at(1))
            ->method('getCustomAttribute')
            ->with('user_type')
            ->willReturn($attributeMock);
        $customerData->expects($this->at(2))
            ->method('getCustomAttribute')
            ->with('ssm_order_flag')
            ->willReturn($attributeMock);
        $customerData->expects($this->at(3))
            ->method('getCustomAttribute')
            ->with('ssm_order_flag')
            ->willReturn($attributeMock);

        $this->customerRepository
            ->expects($this->any())
            ->method('getById')
            ->with($customerId)
            ->will($this->returnValue($customerData));
        $dataModel = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMockForAbstractClass();
        $dataModel->method('setId')->with(231);
        $dataModel->method('setCustomAttribute')->with('ssm_order_flag');

        $this->customerMock->method('getDataModel')->willReturn($dataModel);
        $this->customerMock->method('updateData')->with($dataModel);
        $this->customerFactoryMock->method('create');
        $this->customerFactoryMock->method('saveAttribute')->with($this->customerMock, 'ssm_order_flag');

        $this->resultJsonMock->expects($this->at(1))
            ->method('setData')
            ->with($data1)
            ->willReturnSelf();
        $this->object->execute();
    }
}
