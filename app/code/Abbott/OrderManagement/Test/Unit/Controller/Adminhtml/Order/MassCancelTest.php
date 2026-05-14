<?php
namespace Abbott\OrderManagement\Test\Unit\Controller\Adminhtml\Order;

use Abbott\OrderManagement\Controller\Adminhtml\Order\MassCancel;
use Abbott\OrderManagement\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use ReflectionMethod;

class MassCancelTest extends \PHPUnit\Framework\TestCase
{
    public $helperMock;
    public const ERROR_MESSAGE = "You cannot cancel the order.";
    public const MESSAGE = "Get the current store ID";
    public const DATE_FORMAT = '01.01.1970 00:01';
    public const FORMAT = 'Y-m-d H:i:s';

    protected $contextMock;
    protected $resultRedirectMock;
    protected $orderHelperMock;
    protected $filterMock;
    protected $salesFactoryMock;
    protected $orderInterfaceFactoryMock;
    protected $resultRedirectFactoryMock;
    protected $redirectMock;
    protected $resultFactoryMock;
    protected $messageManagerMock;
    protected $objectManager;
    /**
     * @return void
     */
    public function setUp() : void
    {

        $this->contextMock = $this->getMockBuilder(Context::class)->disableOriginalConstructor()
        ->getMock();

        $this->resultRedirectMock = $this->getMockBuilder(Result::class)->setMethods(['setPath'])
        ->disableOriginalConstructor()
        ->getMock();

        $this->filterMock = $this->getMockBuilder(Filter::class)->disableOriginalConstructor()
        ->getMock();

        $this->salesFactoryMock = $this->getMockBuilder(CollectionFactory::class)->disableOriginalConstructor()
        ->getMock();

        $this->orderInterfaceFactoryMock = $this->getMockBuilder(
            OrderManagementInterface::class
        )->disableOriginalConstructor()
        ->getMock();

        $this->orderHelperMock = $this->getMockBuilder(Data::class)->disableOriginalConstructor()->setMethods(
            [
                'checkIsProgressiveAndBuyersRemorse','getTime'
            ]
        )
        ->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

            $this->redirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

            $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

            $this->messageManagerMock = $this->createMock(ManagerInterface::class, ['addErrorMessage']);

            $this->contextMock->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $this->objectManager = new ObjectManager($this);
        $this->helperMock = $this->objectManager->getObject(
            MassCancel::class,
            [
            'context' => $this->contextMock,
            'filter' => $this->filterMock,
            'collectionFactory' => $this->salesFactoryMock,
            'orderManagement' => null,
            'helper' => $this->orderHelperMock,
            'resultRedirectFactory' => $this->resultRedirectFactoryMock
            ]
        );
    }
        /**
         * @return void
         */
    public function testmassActionone()
    {
        $checkProgressiveAndBuyersRemorse = ['is_progressive'=>1,'is_cancel_order'=>1];
        $testMethod = new ReflectionMethod(MassCancel::class, 'massAction');
        $testMethod->setAccessible(true);

        //***** REDIRECT */

        $this->resultRedirectFactoryMock->expects($this->once())->method('create')->willReturn($this->redirectMock);

        //****** REDIRECT END */
        $orderMock = $this->getMockBuilder(Order::class)
        ->disableOriginalConstructor()
        ->setMethods(['getEntityId','getStatus','getShippingMethod'])
        ->getMock();

        $groupCollectionMock =
        $this->getMockBuilder(
            AbstractCollection::class
        )->setMethods(['getItems', 'load'])->disableOriginalConstructor()->getMockForAbstractClass();

        $orderMock->expects($this->any())->method('getEntityId')->willReturn(2);

        $this->orderHelperMock->expects($this->any())->method('checkIsProgressiveAndBuyersRemorse')
        ->willReturn($checkProgressiveAndBuyersRemorse);

        $errorMessage = self::ERROR_MESSAGE;

        $this->messageManagerMock->expects($this->once())
        ->method('addErrorMessage')
        ->with($errorMessage, null)
        ->willReturnSelf();

        $groupCollectionMock->expects($this->any())->method('getItems')->willReturn([$orderMock]);

        $this->assertEquals($this->redirectMock, $testMethod->invokeArgs(
            $this->helperMock,
            [$groupCollectionMock]
        ), self::MESSAGE);
    }

        /**
         * @return void
         */
    public function testmassActiontwo()
    {
        $checkProgressiveAndBuyersRemorse = ['is_progressive'=>1,'is_cancel_order'=>0];
        $testMethod = new ReflectionMethod(MassCancel::class, 'massAction');
        $testMethod->setAccessible(true);

        //***** REDIRECT */

        $this->resultRedirectFactoryMock->expects($this->once())->method('create')->willReturn($this->redirectMock);

        //****** REDIRECT END */
        $orderMock = $this->getMockBuilder(Order::class)
        ->disableOriginalConstructor()
        ->setMethods(['getEntityId'])
        ->getMock();

        $groupCollectionMock =
        $this->getMockBuilder(AbstractCollection::class)->setMethods(
            ['getItems', 'load']
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $orderMock->expects($this->any())->method('getEntityId')->willReturn(2);

        $this->orderHelperMock->expects($this->any())->method('checkIsProgressiveAndBuyersRemorse')
        ->willReturn($checkProgressiveAndBuyersRemorse);

        $errorMessage = self::ERROR_MESSAGE;

        $this->messageManagerMock->expects($this->once())
        ->method('addErrorMessage')
        ->with($errorMessage, null)
        ->willReturnSelf();

        $groupCollectionMock->expects($this->any())->method('getItems')->willReturn([$orderMock]);

        $this->assertEquals($this->redirectMock, $testMethod->invokeArgs(
            $this->helperMock,
            [$groupCollectionMock]
        ), self::MESSAGE);
    }

        /**
         * @return void
         */
    public function testmassActionthree()
    {
        $dateString = self::DATE_FORMAT;
        $entity_id = 2;
        $status = 'processing';
        $shippingmethod = 'fedex_STANDARD_OVERNIGHT';
        $checkProgressiveAndBuyersRemorse = ['is_progressive'=>1,'is_cancel_order'=>1];
        $testMethod = new ReflectionMethod(MassCancel::class, 'massAction');
        $testMethod->setAccessible(true);

        //***** REDIRECT */

        $this->resultRedirectFactoryMock->expects($this->once())->method('create')->willReturn($this->redirectMock);

        //****** REDIRECT END */
        $orderMock = $this->getMockBuilder(Order::class)
        ->disableOriginalConstructor()
        ->setMethods(['getEntityId','getCreatedAt','getStatus','getShippingMethod'])
        ->getMock();

        $groupCollectionMock =
        $this->getMockBuilder(AbstractCollection::class)->setMethods(
            ['getItems', 'load']
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $orderMock->expects($this->any())->method('getEntityId')->willReturn($entity_id);
        $orderMock->expects($this->any())->method('getStatus')->willReturn($status);
        $orderMock->expects($this->any())->method('getShippingMethod')->willReturn($shippingmethod);

        $this->orderHelperMock->expects($this->any())->method('checkIsProgressiveAndBuyersRemorse')
        ->willReturn($checkProgressiveAndBuyersRemorse);

        $errorMessage = self::ERROR_MESSAGE;

        $this->messageManagerMock->expects($this->once())
        ->method('addErrorMessage')
        ->with($errorMessage, null)
        ->willReturnSelf();

        $orderMock->method('getCreatedAt')
        ->willReturn($dateString);

        $this->orderHelperMock->expects($this->once())->method('getTime')
        ->willReturn(strtotime(date(self::FORMAT)));

        $groupCollectionMock->expects($this->any())->method('getItems')->willReturn([$orderMock]);

        $this->assertEquals($this->redirectMock, $testMethod->invokeArgs(
            $this->helperMock,
            [$groupCollectionMock]
        ), self::MESSAGE);
    }

        /**
         * @return void
         */
    public function testmassActionfour()
    {
        $dateString = self::DATE_FORMAT;
        $entity_id = 2;
        $status = 'processingnot';
        $shippingmethod = 'fedex_STANDARD_OVERNIGHTNOT';
        $checkProgressiveAndBuyersRemorse = ['is_progressive'=>1,'is_cancel_order'=>1];
        $testMethod = new ReflectionMethod(MassCancel::class, 'massAction');
        $testMethod->setAccessible(true);

        //***** REDIRECT */

        $this->resultRedirectFactoryMock->expects($this->once())->method('create')->willReturn($this->redirectMock);

        //****** REDIRECT END */
        $orderMock = $this->getMockBuilder(Order::class)
        ->disableOriginalConstructor()
        ->setMethods(['getEntityId','getCreatedAt','getStatus','getShippingMethod'])
        ->getMock();

        $groupCollectionMock =
        $this->getMockBuilder(AbstractCollection::class)->setMethods(
            ['getItems', 'load']
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $orderMock->expects($this->any())->method('getEntityId')->willReturn($entity_id);
        $orderMock->expects($this->any())->method('getStatus')->willReturn($status);
        $orderMock->expects($this->any())->method('getShippingMethod')->willReturn($shippingmethod);

        $this->orderHelperMock->expects($this->any())->method('checkIsProgressiveAndBuyersRemorse')
        ->willReturn($checkProgressiveAndBuyersRemorse);

        $errorMessage = self::ERROR_MESSAGE;

        $this->messageManagerMock->expects($this->once())
        ->method('addErrorMessage')
        ->with($errorMessage, null)
        ->willReturnSelf();

        $orderMock->method('getCreatedAt')
        ->willReturn($dateString);

        $this->orderHelperMock->expects($this->once())->method('getTime')
        ->willReturn(strtotime(date(self::FORMAT)));

        $groupCollectionMock->expects($this->any())->method('getItems')->willReturn([$orderMock]);

        $this->assertEquals($this->redirectMock, $testMethod->invokeArgs(
            $this->helperMock,
            [$groupCollectionMock]
        ), self::MESSAGE);
    }

        /**
         * @return void
         */
    public function testmassActionfive()
    {
        $dateString = self::DATE_FORMAT;
        $entity_id = 2;
        $status = 'processing';
        $shippingmethod = 'fedex_STANDARD_OVERNIGHTNot';
        $checkProgressiveAndBuyersRemorse = ['is_progressive'=>0,'is_cancel_order'=>0];
        $testMethod = new ReflectionMethod(MassCancel::class, 'massAction');
        $testMethod->setAccessible(true);

        //***** REDIRECT */

        $this->resultRedirectFactoryMock->expects($this->once())->method('create')->willReturn($this->redirectMock);

        //****** REDIRECT END */
        $orderMock = $this->getMockBuilder(Order::class)
        ->disableOriginalConstructor()
        ->setMethods(['getEntityId','getCreatedAt','getStatus','getShippingMethod'])
        ->getMock();

        $groupCollectionMock =
        $this->getMockBuilder(AbstractCollection::class)->setMethods(
            ['getItems', 'load','count']
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $orderMock->expects($this->any())->method('getEntityId')->willReturn($entity_id);
        $orderMock->expects($this->any())->method('getStatus')->willReturn($status);
        $orderMock->expects($this->any())->method('getShippingMethod')->willReturn($shippingmethod);

        $this->orderHelperMock->expects($this->any())->method('checkIsProgressiveAndBuyersRemorse')
        ->willReturn($checkProgressiveAndBuyersRemorse);
        $countNoncancelOrder = 4;
        $errorMessage = '%1 order(s) cannot be canceledaa.';
        $errorMessageManager = __(
            $errorMessage,
            $countNoncancelOrder
        );
        $this->messageManagerMock->expects($this->once())
        ->method('addErrorMessage')
        ->with($errorMessageManager)
        ->willReturnSelf();

        $orderMock->method('getCreatedAt')
        ->willReturn($dateString);

        $this->orderHelperMock->expects($this->once())->method('getTime')
        ->willReturn(strtotime(date(self::FORMAT)));

        $groupCollectionMock->expects($this->any())->method('getItems')->willReturn([$orderMock]);

        $groupCollectionMock->expects($this->any())->method('count')->willReturn(5);

        $this->assertEquals($this->redirectMock, $testMethod->invokeArgs(
            $this->helperMock,
            [$groupCollectionMock]
        ), self::MESSAGE);
    }

        /**
         * @return void
         */
    public function testmassActionsix()
    {
        $dateString = self::DATE_FORMAT;
        $entity_id = 2;
        $status = 'processing';
        $shippingmethod = 'fedex_STANDARD_OVERNIGHTNot';
        $checkProgressiveAndBuyersRemorse = ['is_progressive'=>0,'is_cancel_order'=>0];
        $testMethod = new ReflectionMethod(MassCancel::class, 'massAction');
        $testMethod->setAccessible(true);

        //***** REDIRECT */

        $this->resultRedirectFactoryMock->expects($this->once())->method('create')->willReturn($this->redirectMock);

        //****** REDIRECT END */
        $orderMock = $this->getMockBuilder(Order::class)
        ->disableOriginalConstructor()
        ->setMethods(['getEntityId','getCreatedAt','getStatus','getShippingMethod'])
        ->getMock();

        $groupCollectionMock =
        $this->getMockBuilder(AbstractCollection::class)->setMethods(
            ['getItems', 'load','count']
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $orderMock->expects($this->any())->method('getEntityId')->willReturn($entity_id);
        $orderMock->expects($this->any())->method('getStatus')->willReturn($status);
        $orderMock->expects($this->any())->method('getShippingMethod')->willReturn($shippingmethod);

        $this->orderHelperMock->expects($this->any())->method('checkIsProgressiveAndBuyersRemorse')
        ->willReturn($checkProgressiveAndBuyersRemorse);
        $countNoncancelOrder = 4;
        $errorMessage = '%1 order(s) cannot be canceledaa.';
        $errorMessageManager = __(
            $errorMessage,
            $countNoncancelOrder
        );
        $this->messageManagerMock->expects($this->once())
        ->method('addErrorMessage')
        ->with($errorMessageManager)
        ->willReturnSelf();

        $orderMock->method('getCreatedAt')
        ->willReturn($dateString);

        $this->orderHelperMock->expects($this->once())->method('getTime')
        ->willReturn(strtotime(date(self::FORMAT)));

        $groupCollectionMock->expects($this->any())->method('getItems')->willReturn([$orderMock]);

        $groupCollectionMock->expects($this->any())->method('count')->willReturn(5);

        $this->assertEquals($this->redirectMock, $testMethod->invokeArgs(
            $this->helperMock,
            [$groupCollectionMock]
        ), self::MESSAGE);
    }
}
