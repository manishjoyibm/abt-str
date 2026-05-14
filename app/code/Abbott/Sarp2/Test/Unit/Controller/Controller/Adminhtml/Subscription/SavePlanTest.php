<?php
namespace Abbott\Sarp2\Test\Unit\Controller\Adminhtml\Subscription;

use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\View\LayoutFactory;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\Exception\CouldNotSaveException;

class SavePlanTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    public $objectManager;
    /**
     * @var (\Aheadworks\Sarp2\Api\ProfileRepositoryInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $profilerepositoryMock;
    public $profileManagementMock;
    public $helperMock;
    public $updateSubscribeMock;
    /**
     * @var (\Aheadworks\Sarp2\Api\Data\ProfileInterfaceFactory & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $profileFactoryMock;
    public $contextMock;
    public $messageManager;
    public $requestMock;
    public $resultRedirectFactory;
    /**
     * @var (\Abbott\Sarp2\Controller\Adminhtml\Subscription\Saveplan & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $saveplanMockMethod;
    public $saveplanMock;
    /**
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        

        $this->profilerepositoryMock = $this->createMock(
            \Aheadworks\Sarp2\Api\ProfileRepositoryInterface::class
        );

        $this->profileManagementMock = $this->getMockBuilder(
            \Aheadworks\Sarp2\Api\ProfileManagementInterface::class)->disableOriginalConstructor()->setMethods(['changeSubscriptionPlan','schedule','changeStatusAction','changeShippingAddress','changeNextPaymentDate','changePaymentInformation','getNextPaymentInfo','getAllowedStatuses','isCustomerSubscribedOnProduct'])->getMock();


        $this->helperMock = $this->getMockBuilder(\Abbott\Sarp2\Helper\Data::class)->disableOriginalConstructor()->setMethods(['getUpdateMailEnabled'])->getMock();

        $this->updateSubscribeMock = $this->createPartialMock(
            \Abbott\Sarp2\Helper\ChangeSubscription::class,
            ['updateSubscriptionNotification']
        );

        $this->profileFactoryMock = $this->createMock(\Aheadworks\Sarp2\Api\Data\ProfileInterfaceFactory::class);

        $this->contextMock = $this->createMock(\Magento\Backend\App\Action\Context::class);

        $this->messageManager = $this->createPartialMock(
            \Magento\Framework\Message\Manager::class,
            ['addSuccessMessage', 'addErrorMessage']
        );

        $this->requestMock = $this->getMockBuilder('\Magento\Framework\App\Request\Http')->setMethods(array('getParam'))->disableOriginalConstructor()->getMock();

        $this->contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->setMethods(['getRequest', 'getResponse', 'getMessageManager', 'getSession','getResultRedirectFactory'])
            ->setConstructorArgs(
                $this->objectManager->getConstructArguments(
                    \Magento\Backend\App\Action\Context::class,
                    [
                        'request' => $this->requestMock
                    ]
                )
            )
            ->getMock();
        
        $this->contextMock->expects($this->once())->method('getRequest')->will($this->returnValue($this->requestMock));

        $this->contextMock->expects($this->once())
        ->method('getMessageManager')
        ->will($this->returnValue($this->messageManager));

        $this->resultRedirectFactory = $this->getMockBuilder(\Magento\Backend\Model\View\Result\RedirectFactory::class)
        ->disableOriginalConstructor()
        ->setMethods(['create'])
        ->getMock();

        $this->contextMock->expects($this->any())->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);

        $this->saveplanMockMethod = $this->getMockBuilder(\Abbott\Sarp2\Controller\Adminhtml\Subscription\Saveplan::class)
        ->disableOriginalConstructor()->setMethods(array('execute','performSave'))->getMock(); 

        $this->saveplanMock = $this->objectManager->getObject(
            \Abbott\Sarp2\Controller\Adminhtml\Subscription\Saveplan::class,
            [
              'context' => $this->contextMock,
              'ProfileInterfaceFactory' => $this->profileFactoryMock,
              'ProfileRepositoryInterface' => $this->profilerepositoryMock,
              'ProfileManagementInterface' => $this->profileManagementMock,
              'helper' => $this->helperMock,
              'updateSubscribe' => $this->updateSubscribeMock
            ]
        );
    }

    public function testExecute()
    { 
        $profileId = 5;
        $subscriptionplan = 6;

        $this->requestMock->expects($this->at(0))
        ->method('getParam')->with('profile_id')
        ->will($this->returnValue(5));

        $this->requestMock->expects($this->at(1))
        ->method('getParam')->with('aw_sarp2_subscription_type')
        ->will($this->returnValue(5));

        $this->messageManager->expects($this->at(0))
            ->method('addSuccessMessage')
            ->with('Subscription Plan has been successfully changed.');
           
        $resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();

        $resultRedirect->expects($this->any())->method('setPath')
            ->with('*/*/view/profile_id/5', [])
            ->willReturnSelf();

        $this->resultRedirectFactory->expects($this->any())
            ->method('create')
            ->willReturn($resultRedirect);

        $this->saveplanMock->execute();

        $testMethod = new \ReflectionMethod(
            \Abbott\Sarp2\Controller\Adminhtml\Subscription\Saveplan::class,
            'performSave'
        );
        $testMethod->setAccessible(true);
    }
    public function testExecuteWithException()
    {
        $exception = new \Exception('Exception');
        $this->messageManager->expects($this->any())
            ->method('addErrorMessage')
            ->with(__('We can\'t delete the address right now.'))
            ->willThrowException($exception);


        $resultRedirect = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
        ->setMethods(['setPath'])
        ->disableOriginalConstructor()
        ->getMock();

    $resultRedirect->expects($this->any())->method('setPath')
        ->with('*/*/view/profile_id/0', [])
        ->willReturnSelf();

    $this->resultRedirectFactory->expects($this->any())
        ->method('create')
        ->willReturn($resultRedirect);
        $this->assertSame($resultRedirect, $this->saveplanMock->execute());

    }

    public function testPerformSave()
    {
        $profileId = 5;
        $subscriptionplan = 6;
        $this->profileManagementMock->expects($this->any())->method('changeSubscriptionPlan')->with($profileId,$subscriptionplan)->will($this->returnValue($this->profileManagementMock));

        $this->helperMock->expects($this->any())->method('getUpdateMailEnabled')->willReturn($this->returnValue('yes')); 
       
        $this->updateSubscribeMock->expects($this->any())->method('updateSubscriptionNotification')->will($this->returnValue($this->updateSubscribeMock)); 

        $testMethod = new \ReflectionMethod(
            \Abbott\Sarp2\Controller\Adminhtml\Subscription\Saveplan::class,
            'performSave'
        );
        $testMethod->setAccessible(true);
    }

    public function testPerformSaveexe()
    {
        $profileId = 5;
        $subscriptionplan = 6;
        $this->profileManagementMock->expects($this->any())->method('changeSubscriptionPlan')->with($profileId,$subscriptionplan)->will($this->returnValue($this->profileManagementMock));

        $this->updateSubscribeMock->expects($this->any())->method('updateSubscriptionNotification')->will($this->returnValue($this->updateSubscribeMock)); 

        $testMethod = new \ReflectionMethod(
            \Abbott\Sarp2\Controller\Adminhtml\Subscription\Saveplan::class,
            'performSave'
        );
        $testMethod->setAccessible(true);
    }
}
