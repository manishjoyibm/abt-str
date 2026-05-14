<?php

namespace Abbott\Analytics\Block;

use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Api\Data\OrderInterface;

class PriceSpider extends \Magento\Checkout\Block\Onepage\Success
{
    public $orderCollectionFactory;
    public $orderFactory;
    public $productRepositoryFactory;
    public $cookieManager;
    protected $logger;

    /**
     * constructor.
     * @param Context $context
     * @param Session $checkoutSession
     * @param OrderConfig $orderConfig
     * @param HttpContext $httpContext
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param OrderInterface $orderFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        OrderConfig $orderConfig,
        HttpContext $httpContext,
        OrderCollectionFactory $orderCollectionFactory,
        OrderInterface $orderFactory,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderFactory = $orderFactory;
        $this->productRepositoryFactory = $productRepositoryFactory;
        $this->cookieManager = $cookieManager;
        $this->logger = $logger;
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
    }

    /**
     * get psrid.
     *
     * @return string
     */
    public function getPsrId()
    {
        return $this->cookieManager->getCookie('abt_psrid');
    }

    /**
     * get PriceSpider order data.
     *
     * @return array
     */
    public function getPsData()
    {
        $incrementId = $this->_checkoutSession->getLastRealOrder()->getIncrementId();
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter('increment_id', ['eq' => $incrementId]);
        $orderEntity = $orderCollection->getFirstItem();
        $orderData = $orderEntity->getData();
        return [
          'psrid' => $this->getPsrId(),
          'purchasedate' => $orderData['created_at'],
          'purchasetotal' => number_format($orderData['subtotal'], 2),
          'purchasediscount' => abs(floatval(preg_replace('/[^\d.]/', '', number_format($orderData['discount_amount'], 2)))),
          'currencycode' => $orderData['order_currency_code']
        ];
    }

    /**
     * get order object.
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrderData()
    {
        $orderId = $this->_checkoutSession->getData('last_order_id');
        return $this->orderFactory->load($orderId);
    }

    /**
     * get PriceSpider order items data.
     *
     * @return array
     */
    public function getProductsData()
    {
        $items = [];
        foreach ($this->getOrderData()->getAllItems() as $item) {
            $product = $this->productRepositoryFactory->create()->getById($item->getProductId());
            $brand = $product->getData('brand');
            $items[] = [
              'productName' => $product->getName(),
              'sku' => $product->getSku(),
              'unitPrice' => number_format($item->getPrice(), 2),
              'manufacturer' => $brand ? $brand : "NA",
              'quantity' => ceil($item->getQtyOrdered())
            ];
        }
        return $items;
    }

    /**
     * @return bool|string
     */
    public function getProductsDataJson()
    {
        return \Magento\Framework\Serialize\JsonConverter::convert($this->getProductsData());
    }
}
