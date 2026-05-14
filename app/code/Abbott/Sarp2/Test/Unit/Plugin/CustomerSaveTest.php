<?php

namespace Abbott\Sarp2\Test\Unit\Plugin;
use Magento\Backend\Model\View\Result\RedirectFactory;

/**
 * Class CustomerSaveTest
 */
class CustomerSaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var (\Magento\Framework\App\RequestInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $requestMock;
    public $redirectFactoryMock;
    public $customerSave;
    public $resultRedirect;
    public $helper;
    public $contextpostMock;
    public $setpathpostMock;
    /**
     * @var \Magento\Framework\Controller\ResultFactory\
     */
    protected $resultFactory;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface\
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory\
     */
    protected $redirectFactory;

    /**
     * @return void
     */
    protected function setUp(): void
    {

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getPostValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        /* Mock Subscription Profile Collection Data */
        // $this->resultFactory = $this->createMock(\Magento\Framework\Controller\ResultFactory::class);
        $this->redirect = $this->createMock(
            \Magento\Framework\App\Response\RedirectInterface::class);
        // $this->redirectFactory = $this->createMock(
        //         \Magento\Framework\Controller\Result\RedirectFactory::class);
        
        $this->redirectFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->customerSave = $this->createMock(
            \Magento\Customer\Controller\Address\FormPost::class);
        
            $this->resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();


        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirect);


        $this->helper = new \Abbott\Sarp2\Plugin\CustomerSave($this->resultFactory, $this->redirect, $this->redirectFactoryMock);
    }
    /**
     * @return void
     */
    public function testAfterExecute()
    {
        $bool = true;
        $refererUrl = 'referer_url';

        $this->resultFactory->expects($this->once())
        ->method('create')
        ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
        ->willReturn($this->resultFactory);

        $this->contextpostMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getPostValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();


        $this->customerSave->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->contextpostMock);

        $this->contextpostMock->expects($this->once())
            ->method('getPostValue')
            ->with('subscription_profile_address_save')
            ->willReturn($bool); 


        $this->resultRedirect->expects($this->once())
            ->method('setUrl')
            ->with('')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->helper->afterExecute($this->customerSave));

            //************************* */

		
    }

    /**
     * @return void
     */
    public function testAfterExecuteNull()
    {
        $bool = false;
        $refererUrl = 'customer/address/index';

        $this->resultFactory->expects($this->once())
        ->method('create')
        ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
        ->willReturn($this->resultFactory);

        $this->contextpostMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getPostValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();


        $this->customerSave->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->contextpostMock);
        
        $this->contextpostMock->expects($this->once())
            ->method('getPostValue')
            ->with('subscription_profile_address_save')
            ->willReturn($bool); 


        //************************* */

        //*************START */

        

        $this->setpathpostMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
        ->setMethods(['setPath'])
        ->disableOriginalConstructor()
        ->getMock();

        $this->redirectFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->setpathpostMock);
        $this->setpathpostMock
            ->expects($this->once())
            ->method('setPath')
            ->with('customer/address/index')
            ->willReturnSelf();

        //*************END */

        $this->assertEquals($this->setpathpostMock, $this->helper->afterExecute($this->customerSave));

		
    }
    
}
