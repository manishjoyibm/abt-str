<?php

namespace Abbott\AbbottReport\Model\Export;

use Magento\Setup\Exception;

class Promo extends \Magento\Framework\Model\AbstractModel
{
    public $directory_list;
    public $_orderCollectionFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $_scopeConfig;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    public $_customerRepositoryInterface;
    /**
     * @var \Magento\Customer\Model\ResourceModel\GroupRepository
     */
    public $_groupRepository;
    public $_addressFactory;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    public $_productRepository;
    const DELIMITER = "\t";
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory
     */
    protected $shipmentItemCollectionFactory;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;
    /**
     * @var \Magento\Customer\Model\ResourceModel\GroupRepository
     */
    protected $groupRepository;
    /**
     * @var \Magento\Sales\Model\Order\AddressFactory
     */
    protected $addressFactory;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;
    /**
     * @var \Magento\SalesRule\Model\Coupon
     */
    protected $couponModel;
    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $ruleModel;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;

    /**
     * Exportdata constructor.
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory $shipmentItemCollectionFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Customer\Model\ResourceModel\GroupRepository $groupRepository
     * @param \Magento\Sales\Model\Order\AddressFactory $addressFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\SalesRule\Model\Coupon $couponModel
     * @param \Magento\SalesRule\Model\Rule $ruleModel
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Framework\Filesystem\Driver\File $file
     */
    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory $shipmentItemCollectionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Model\ResourceModel\GroupRepository $groupRepository,
        \Magento\Sales\Model\Order\AddressFactory $addressFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\SalesRule\Model\Coupon $couponModel,
        \Magento\SalesRule\Model\Rule $ruleModel,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\Filesystem\Driver\File $file
    ) {
        $this->directory_list = $directoryList;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->dateTime = $dateTime;
        $this->shipmentItemCollectionFactory = $shipmentItemCollectionFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_groupRepository = $groupRepository;
        $this->_addressFactory = $addressFactory;
        $this->_productRepository = $productRepository;
        $this->couponModel = $couponModel;
        $this->ruleModel = $ruleModel;
        $this->messageManager = $messageManager;
        $this->csvProcessor = $csvProcessor;
        $this->file = $file;
    }

    /**
     * Main Function To Export Promo Orders Data
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function exportPromoData($data)
    {
        try {
            $startDate = date(
                "Y-m-d H:i:s",
                strtotime('+23 hours 59 minutes', strtotime($data['from_promo']))
            ); // start date
            $endDate = date("Y-m-d H:i:s", strtotime($data['to_promo'])); // end date
            $ordercollection = $this->_orderCollectionFactory->create()
                ->addFieldToFilter('created_at', ['to'=>$startDate, 'from'=>$endDate])
                ->addFieldToFilter('store_id', ['eq' => $data['store_id']])
                ->addFieldToFilter('coupon_code', ['neq' => '']);
            $datacollectionSize = $ordercollection->getSize();
            $newdata[] = $this->getHeadRowValues();


            if ($datacollectionSize > 0) {
                $filepath = $this->getPromoFilePath();

                foreach ($ordercollection as $order) {
                    $newdata[] =$this->getCommonValues($order);
                }

                $this->csvProcessor->setDelimiter(',')->setEnclosure('"')->saveData($filepath, $newdata);
                return $this->getPromoFileName();
            } else {
                $this->messageManager->addError(__('No Data To Fetch For this Report'));
                return null;
            }
        } catch (Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            return null;
        }
    }
    /**
     * For Loading the values to write in file
     *
     * @param array $data Customer Data
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCommonValues($order)
    {
        $orderItemNames = $this->getOrderItemNames($order);
        $orderItemSku = $this->getOrderItemSku($order);
        $shippingAddressId = $order->getShippingAddressId();
        $shippingAddress = $this->_addressFactory->create()
            ->load($shippingAddressId);
        $street = $shippingAddress->getStreet();
        $street2 = (count($street) > 1) ? $street[1] : null;
        return [
            $order->getCreatedAt(),
            $order->getIncrementId(),
            $order->getStatus(),
            $order->getCouponCode(),
            abs(round($order->getDiscountAmount(), 2)),
            $orderItemNames,
           $orderItemSku,
            $order->getGrandTotal(),
            $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
            $street[0],
            $street2,
            $shippingAddress->getCity(),
            $shippingAddress->getRegion(),
            $shippingAddress->getPostcode(),
            $order->getCustomerEmail(),
            $shippingAddress->getTelephone(),
            0
            ];
    }

    /**
     * For Adding Header Row
     *
     * @return array
     */
    public function getHeadRowValues()
    {
        return [
            "Order Date",
            "Order Number",
            "Order Status",
            'Coupon Name',
            'Coupon Amount',
            'Order Items',
         'Order SKU',
            "Order Total",
            "Customer Name",
            "Address1",
            "Address2",
            "City",
            "State",
            "PostalCode",
            "Email",
            "Phone",
            "ReferralGroup",
        ];
    }

    /**
     * For Getting Promo Filename
     *
     * @return string
     */
    public function getPromoFileName()
    {
        $systemFilename="promocode_report";

        return $systemFilename . '_' . $this->dateTime->date('Ymd') . '.csv';
    }
    /**
     * For Promo File Path
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getPromoFilePath()
    {
        $varPath = $this->directory_list->getPath('var') . '/';
        return $varPath . $this->getPromoFileName();
    }
    public function getOrderItemNames($order)
    {
        $namearr = [];
        $orderitems = $order->getAllVisibleItems();
        foreach ($orderitems as $item) {
            $namearr[]=$item->getName();
        }
        return implode("|", $namearr);
    }



    /**
     * get OrderItems SKU
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory  $order
     * @return string
     */
    public function getOrderItemSku($order)
    {
        $orderitems = $order->getAllVisibleItems();
        $skuArray = [];
        foreach ($orderitems as $item) {
            $skuArray[]=$item->getSku();
        }
        return implode("|", $skuArray);
    }
}
