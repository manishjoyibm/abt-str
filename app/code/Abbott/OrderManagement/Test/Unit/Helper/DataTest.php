<?php
namespace Abbott\OrderManagement\Test\Unit\Helper;

use Abbott\ProgressiveDiscount\Helper\Data;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\Profile\Order;
use Aheadworks\Sarp2\Model\Profile\OrderFactory;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Order\Collection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DB\Select;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Sales\Model\OrderRepository;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Log\LoggerInterface;

class DataTest extends TestCase
{
    /**
     * @var (\Magento\Store\Model\StoreManagerInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $storeMock;
    /**
     * @var (\Magento\Framework\App\Helper\Context & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $contextMock;
    /**
     * @var (\Magento\Framework\UrlInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $urlMock;
    /**
     * @var (\Magento\Framework\Stdlib\DateTime\DateTime & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $dateMock;
    /**
     * @var (\Magento\Catalog\Api\ProductRepositoryInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $productRepoIntefaceMock;
    /**
     * @var (\Magento\Framework\Mail\Template\TransportBuilder & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $transportMock;
    /**
     * @var (\Magento\Sales\Model\OrderRepository & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $OrderMock;
    /**
     * @var (\Magento\Directory\Model\RegionFactory & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $regionMock;
    /**
     * @var (\Magento\Directory\Model\CountryFactory & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $countryMock;
    /**
     * @var (\Magento\Framework\Translate\Inline\StateInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $stateMock;
    /**
     * @var (\Aheadworks\Sarp2\Api\ProfileRepositoryInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $profileRepoInterMock;
    /**
     * @var (\Abbott\ProgressiveDiscount\Helper\Data & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $progressiveHelperMock;
    /**
     * @var (\PHPUnit\Framework\MockObject\MockObject & \Psr\Log\LoggerInterface)
     */
    public $loggerMock;
    public $sns1Mock;
    public $sns2Mock;
    /**
     * @var (\Aheadworks\Sarp2\Model\ResourceModel\Profile\Order\Collection & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $collectionMock;
    /**
     * @var (\Aheadworks\Sarp2\Model\Profile\OrderFactory & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $_snsFactory;
    /**
     * @var (\Aheadworks\Sarp2\Model\ResourceModel\Profile\Collection & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $_itemCollection;
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    public $objectManager;
    public $helperMock;
    /**
     * @var (\Magento\Framework\DB\Select & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $selectMock;
    public $dbSelectMock;
    /**
     * @var LayoutFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    /**
     * @var [type]
     */
    protected $layoutFactoryMock;
    /**
     * @var [type]
     */
    protected $request;
    /**
     * @var [type]
     */
    protected $helper;
    protected $layout;
    /**
     * @var [type]
     */
    protected $snsMock;
    /**
     * @return void
     */
    public function setUp() : void
    {

        $this->storeMock = $this->getMockBuilder(StoreManagerInterface::class)->disableOriginalConstructor()
        ->getMock();

        $this->contextMock = $this->getMockBuilder(Context::class)->disableOriginalConstructor()
        ->getMock();

        $this->urlMock = $this->getMockBuilder(UrlInterface::class)->disableOriginalConstructor()
        ->getMock();

        $this->dateMock = $this->getMockBuilder(DateTime::class)->disableOriginalConstructor()
        ->getMock();

        $this->productRepoIntefaceMock = $this->getMockBuilder(
            ProductRepositoryInterface::class
        )->disableOriginalConstructor()
        ->getMock();

        $this->transportMock = $this->getMockBuilder(TransportBuilder::class)->disableOriginalConstructor()
        ->getMock();

        $this->OrderMock = $this->getMockBuilder(OrderRepository::class)->disableOriginalConstructor()
        ->getMock();

        $this->regionMock = $this->getMockBuilder(
            RegionFactory::class
        )->disableOriginalConstructor()
        ->getMock();

        $this->countryMock = $this->getMockBuilder(
            CountryFactory::class
        )->disableOriginalConstructor()
        ->getMock();

        $this->stateMock = $this->getMockBuilder(
            StateInterface::class
        )->disableOriginalConstructor()
        ->getMock();

        $this->profileRepoInterMock = $this->getMockBuilder(
            ProfileRepositoryInterface::class
        )->disableOriginalConstructor()
        ->getMock();

        $this->progressiveHelperMock = $this->getMockBuilder(
            Data::class
        )->disableOriginalConstructor()
        ->getMock();

        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->sns1Mock = $this->getMockBuilder(
            Order::class
        )->disableOriginalConstructor()->getMock();

        $this->sns1Mock = $this->createMock(Order::class);

        $this->sns2Mock = $this->createMock(Collection::class, ['addFieldToFilter','getSelect']);

        $this->collectionMock = $this->getMockBuilder(Collection::class)->setMethods(['getSelect'])
        ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->_snsFactory = $this->getMockBuilder(OrderFactory::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->_itemCollection = $this->getMockBuilder(\Aheadworks\Sarp2\Model\ResourceModel\Profile\Collection::class)->setMethods(['addFieldToFilter'])
        ->disableOriginalConstructor()
        ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->helperMock = $this->objectManager->getObject(
            \Abbott\OrderManagement\Helper\Data::class,
            [
            'storeManager' => $this->storeMock,
            'context' => $this->contextMock,
            'urlInterface' => $this->urlMock,
            'date' => $this->dateMock,
            'productRepository' => $this->productRepoIntefaceMock,
            'transportBuilder' => $this->transportMock,
            'orderRepository' => $this->OrderMock,
            'regionFactory' => $this->regionMock,
            'countryMock' => $this->countryMock,
            'inlineTranslation' => $this->stateMock,
            'profileRepository' => $this->profileRepoInterMock,
            'data' => $this->progressiveHelperMock,
            'sns_order' => $this->sns1Mock
            ]
        );
    }

    /**
     * @return [type]
     */
    public function testCheckIsProgressiveAndBuyersRemorse()
    {
        $orderId = 1123;
        $tableName = 'aw_sarp2_plan';
        $returnArr = ['is_progressive' => 1,
            'is_cancel_order' => 1];
        $snsarr = [
            [
                'id' => 142502,
                'order_id' => '198828',
                'profile_id' => '43460',
                'order_increment_id' => '4000000393',
                'order_date' => '2020-10-09 14:10:41',
                'base_grand_total' => '113.5600',
                'grand_total' => '113.5600',
                'base_currency_code' => 'USD',
                'order_currency_code' => 'USD',
                'order_status' => 'processing',
                'is_progressive' => 1,
                'is_cancel_order' => 1
            ],
            [
                'id' => 142503,
                'order_id' => '198828',
                'profile_id' => '43460',
                'order_increment_id' => '4000000393',
                'order_date' => '2020-10-09 14:10:41',
                'base_grand_total' => '113.5600',
                'grand_total' => '113.5600',
                'base_currency_code' => 'USD',
                'order_currency_code' => 'USD',
                'order_status' => 'processing',
                'is_progressive' => 1,
                'is_cancel_order' => 1
            ]
        ];
        $this->sns1Mock->expects($this->any())->method('getCollection')->willReturn($this->sns2Mock);

        $this->selectMock = $this->getMockBuilder(Select::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->sns2Mock->expects($this->atLeastOnce())
            ->method('addFieldToFilter')
            ->willReturn($this->sns1Mock);

            $this->sns1Mock->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn($snsarr);

         //***** JOIN START */

            $this->dbSelectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

            $this->dbSelectMock->expects($this->any())
            ->method('join')
            ->with(['profile' => $tableName], 'profile.profile_id = main_table.profile_id', [])
            ->willReturn($this->sns1Mock);

            $this->dbSelectMock->expects($this->any())
            ->method('join')
            ->with(['plan' => 'aw_sarp2_plan'], 'plan.plan_id = profile.plan_id', [])
            ->willReturn($this->sns1Mock);

        //***** JOIN END */
        $this->assertSame($returnArr, $this->helperMock->checkIsProgressiveAndBuyersRemorse($orderId));
    }

    /**
     * @return [type]
     */
    public function testCheckIsProgressiveAndBuyersRemorseempty()
    {
        $orderId = 1123;
        $tableName = 'aw_sarp2_plan';
        $snsarr = [];
        $this->sns1Mock->expects($this->any())->method('getCollection')->willReturn($this->sns2Mock);

        $this->selectMock = $this->getMockBuilder(Select::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->sns2Mock->expects($this->atLeastOnce())
            ->method('addFieldToFilter')
            ->willReturn($this->sns1Mock);

            $this->sns1Mock->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn($snsarr);

         //***** JOIN START */

            $this->dbSelectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

            $this->dbSelectMock->expects($this->any())
            ->method('join')
            ->with(['profile' => $tableName], 'profile.profile_id = main_table.profile_id', [])
            ->willReturn($this->sns1Mock);

            $this->dbSelectMock->expects($this->any())
            ->method('join')
            ->with(['plan' => 'aw_sarp2_plan'], 'plan.plan_id = profile.plan_id', [])
            ->willReturn($this->sns1Mock);

        //***** JOIN END */

       //$this->helperMock->checkIsProgressiveAndBuyersRemorse($orderId);
        $this->assertSame($snsarr, $this->helperMock->checkIsProgressiveAndBuyersRemorse($orderId));
    }
}
