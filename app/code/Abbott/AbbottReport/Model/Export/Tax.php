<?php

namespace Abbott\AbbottReport\Model\Export;

use Magento\Setup\Exception;

class Tax extends \Magento\Framework\Model\AbstractModel
{
    public $directory_list;
    public $_creditmemoCollectionFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $_scopeConfig;
    public $_addressFactory;
    public $_productRepository;
    const ENCLOSURE = '"';
    const DELIMITER = "\t";
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory
     */
    protected $creditmemoCollectionFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;
    /**
     * @var \Magento\Sales\Model\Order\AddressFactory
     */
    protected $addressFactory;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;
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
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditmemoCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Sales\Model\Order\AddressFactory $addressFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\File\Csv $csvProcessor
     */
    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditmemoCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Sales\Model\Order\AddressFactory $addressFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\File\Csv $csvProcessor
    ) {
        $this->directory_list = $directoryList;
        $this->_creditmemoCollectionFactory = $creditmemoCollectionFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->dateTime = $dateTime;
        $this->_addressFactory = $addressFactory;
        $this->_productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->messageManager = $messageManager;
        $this->file = $file;
        $this->csvProcessor = $csvProcessor;
    }

    /**
     * Main Function To Export Tax Orders Data
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function exportTaxData($data)
    {
        try {
            $startDate = date(
                "Y-m-d H:i:s",
                strtotime('+23 hours 59 minutes', strtotime($data['from_tax']))
            ); // start date
            $endDate = date("Y-m-d H:i:s", strtotime($data['to_tax'])); // end date
            $creditcollection = $this->_creditmemoCollectionFactory->create()
                ->addFieldToFilter('created_at', ['to'=>$startDate, 'from'=>$endDate])
                ->addFieldToFilter('store_id', ['eq' => $data['store_id']])
                ->addFieldToFilter('grand_total', ['gt' => 0]);
            $datacollectionSize = $creditcollection->getSize();
            if ($datacollectionSize > 0) {
                $filepath = $this->getTaxFilePath();
                $newdata[] =  $this->getHeadRowValues();
                foreach ($creditcollection as $credit) {
                    $order= $this->orderRepository->get($credit->getOrderId());
                    $credititems = $credit->getAllItems();
                    foreach ($credititems as $item) {
                        $newdata[] =  $this->getCommonValues($order, $credit, $item);
                    }
                }
                $this->csvProcessor->setDelimiter(',')->setEnclosure('"')->saveData($filepath, $newdata);
                return $this->getTaxFileName();
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
    public function getCommonValues($order, $credit, $item)
    {
        $shippingAddressId = $order->getShippingAddressId();
        $shippingAddress = $this->_addressFactory->create()
            ->load($shippingAddressId);
        $street = $shippingAddress->getStreet();
        $street2 = (count($street) > 1) ? $street[1] : null;
        $brand = $this->getProductBrand($item->getSku());
        return [
            $order->getCreatedAt(),
            $credit->getCreatedAt(),
            $item->getSku(),
            $brand,
            $street[0],
            $street2,
            $shippingAddress->getCity(),
            $shippingAddress->getRegion(),
            $shippingAddress->getPostcode(),
            $order->getGrandTotal(),
            $item->getTaxAmount(),
            round($credit->getGrandTotal(), 2),
            0,
            round($credit->getGrandTotal(), 2),
            $order->getIncrementId(),
            $order->getCustomerEmail(),
            abs(round($credit->getDiscountAmount(), 2))
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
            "Credit Date",
            "SKU",
            "Brand",
            "Shipping Addr1",
            "Shipping Addr2",
            "Shipping City",
            "Shipping State",
            "Shipping Zip",
            "Order Total",
            "Item Tax",
            "Credit Amt",
            "Credit Type",
            "Order Credit Total",
            "Order Number",
            "Email",
            "Item Coupon Amt",
        ];
    }

    /**
     * For Getting Tax Filename
     *
     * @return string
     */
    public function getTaxFileName()
    {
        $systemFilename="Tax Report";

        return $systemFilename . '_' . $this->dateTime->date('Ymd') . '.csv';
    }
    /**
     * For Tax File Path
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getTaxFilePath()
    {
        $varPath = $this->directory_list->getPath('var') . '/';
        return $varPath . $this->getTaxFileName();
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
}
