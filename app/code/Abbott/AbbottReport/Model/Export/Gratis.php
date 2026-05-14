<?php

namespace Abbott\AbbottReport\Model\Export;

use Magento\Setup\Exception;

class Gratis extends \Magento\Framework\Model\AbstractModel
{
    public $directory_list;
    public $_orderCollectionFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $_scopeConfig;
    public $_groupRepository;
    public $_addressFactory;
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
     * @var \Magento\Sales\Model\Order\Shipment\ItemFactory
     */
    protected $shipmentItemCollectionFactory;
    /**
     * @var \Magento\Sales\Model\Order\Shipment
     */
    protected $shipmentFactory;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerModel;
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
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * Exportdata constructor.
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Sales\Model\Order\Shipment\ItemFactory $shipmentItemCollectionFactory
     * @param \Magento\Sales\Model\Order\Shipment $shipmentFactory
     * @param \Magento\Customer\Model\Customer $customerModel
     * @param \Magento\Customer\Model\ResourceModel\GroupRepository $groupRepository
     * @param \Magento\Sales\Model\Order\AddressFactory $addressFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\SalesRule\Model\Coupon $couponModel
     * @param \Magento\SalesRule\Model\Rule $ruleModel
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\File\Csv $csvProcessor
     */
    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Sales\Model\Order\Shipment\ItemFactory $shipmentItemCollectionFactory,
        \Magento\Sales\Model\Order\Shipment $shipmentFactory,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Customer\Model\ResourceModel\GroupRepository $groupRepository,
        \Magento\Sales\Model\Order\AddressFactory $addressFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\SalesRule\Model\Coupon $couponModel,
        \Magento\SalesRule\Model\Rule $ruleModel,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\File\Csv $csvProcessor
    ) {
        $this->directory_list = $directoryList;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->dateTime = $dateTime;
        $this->shipmentItemCollectionFactory = $shipmentItemCollectionFactory;
        $this->shipmentFactory = $shipmentFactory;
        $this->customerModel = $customerModel;
        $this->_groupRepository = $groupRepository;
        $this->_addressFactory = $addressFactory;
        $this->_productRepository = $productRepository;
        $this->couponModel = $couponModel;
        $this->ruleModel = $ruleModel;
        $this->messageManager = $messageManager;
        $this->file = $file;
        $this->csvProcessor = $csvProcessor;
    }

    /**
     * Main Function To Export Gratis Orders Data
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function exportGratisData($data)
    {
        try {


            $startDate = date(
                "Y-m-d H:i:s",
                strtotime('+23 hours 59 minutes', strtotime($data['from_gratis']))
            ); // start date
            $endDate = date("Y-m-d H:i:s", strtotime($data['to_gratis'])); // end date
            $ordercollection = $this->_orderCollectionFactory->create()
                ->addFieldToFilter('created_at', ['to'=>$startDate, 'from'=>$endDate])
                ->addFieldToFilter('store_id', ['eq' => $data['store_id']])
                ->addFieldToFilter('grand_total', ['eq' => 0]);
            $datacollectionSize = $ordercollection->getSize();
            if ($datacollectionSize > 0) {

                $filepath = $this->getGratisFilePath();
                $newdata[] =  $this->getHeadRowValues();
                foreach ($ordercollection as $order) {
                    $orderitems = $order->getAllVisibleItems();
                    foreach ($orderitems as $item) {
                        $newdata[] =  $this->getCommonValues($order, $item);
                    }
                }

                $this->csvProcessor->setDelimiter(',')->setEnclosure('"')->saveData($filepath, $newdata);
                return $this->getGratisFileName();
            } else {
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
    public function getCommonValues($order, $item)
    {
        $orderItemId = $item->getId();
        $shipmentdate = $this->getShipmentDate($orderItemId);
        $tier = ($order->getCustomerIsGuest()!=1) ? $this->getCustomerGroup($order->getCustomerEmail(), $order->getStoreId()) : "Guest";
        $shippingAddressId = $order->getShippingAddressId();
        $shippingAddress = $this->_addressFactory->create()
            ->load($shippingAddressId);
        $street = $shippingAddress->getStreet();
        $street2 = (count($street) > 1) ? $street[1] : null;
        $brand = $this->getProductBrand($item->getSku());
        $couponDesc= $this->getCouponDesc($order->getCouponCode());
        return [
            $shipmentdate,
            $order->getCreatedAt(),
            $order->getCustomerEmail(),
            $tier,
            $street[0],
            $street2,
            $shippingAddress->getCity(),
            $shippingAddress->getRegion(),
            $shippingAddress->getPostcode(),
            $order->getIncrementId(),
            $brand,
            $item->getSku(),
            $item->getQtyOrdered(),
            $order->getCouponCode(),
            $couponDesc,
            $item->getDiscountAmount(),
            $order->getDiscountAmount(),
            $order->getGrandTotal(),
            $order->getStatus()
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
            "Ship Date",
            "Order Date",
            "Customer Email",
            "Tier",
            "Shipping Addr1",
            "Shipping Addr2",
            "Shipping City",
            "Shipping State",
            "Shipping Zip",
            "Order Number",
            "Brand",
            "SKU",
            "Quantity",
            "Order Coupon Code",
            "Coupon Short Desc",
            "Item Promo Amount",
            "Order Promo Amount",
            "Order Total",
            "Order Status",
        ];
    }


    /**
     * For Getting Gratis Filename
     *
     * @return string
     */
    public function getGratisFileName()
    {
        $systemFilename="Report_Gratis";

        return $systemFilename . '_' . $this->dateTime->date('Y-m-d_H:i:s') . '.csv';
    }
    /**
     * For Gratis File Path
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getGratisFilePath()
    {
        $varPath = $this->directory_list->getPath('var') . '/export/';
        return $varPath . $this->getGratisFileName();
    }

    /**
     * For Shipment Date
     *
     * @param $orderItemId
     * @return string|null
     */
    public function getShipmentDate($orderItemId)
    {
        $shipmentitem = $this->shipmentItemCollectionFactory->create();
        $shipmentitem->load($orderItemId, 'order_item_id');

        $shipmentid= ($shipmentitem->getId()) ? $shipmentitem->getParentId() : null;
        if ($shipmentid) {
            $shipment = $this->shipmentFactory->load($shipmentid);
            return $shipment->getCreatedAt();
        } else {
            return null;
        }
    }

    /**
     * @param $customerEmail
     * @param $websiteid
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerGroup($customerEmail, $websiteid)
    {
        $customer = $this->customerModel
            ->setWebsiteId($websiteid)
            ->loadByEmail($customerEmail);
        if ($customer->getId()) {
            $customerGroupId = $customer->getGroupId();
            $customerGroup = $this->_groupRepository->getById($customerGroupId);
            return $customerGroup->getCode();
        } else {
            return "Guest";
        }
    }

    /**
     * @param $productId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductBrand($productId)
    {
        $product = $this->_productRepository->get($productId);
        return $product->getBrand();
    }
    public function getCouponDesc($couponcode)
    {
        if ($couponcode) {
            $couponByCode = $this->couponModel->loadByCode($couponcode);
            $couponByRuleId = $this->ruleModel->load($couponByCode['rule_id']);
            return isset($couponByRuleId['description']) ? $couponByRuleId['description'] : '';
        } else {
            return null;
        }
    }
}
