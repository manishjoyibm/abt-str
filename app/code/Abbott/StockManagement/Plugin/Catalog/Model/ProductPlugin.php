<?php


namespace Abbott\StockManagement\Plugin\Catalog\Model;

use Magento\Catalog\Model\Product;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Request\Http;
use Magento\Customer\Model\Session as CustomerSession;

class ProductPlugin
{
    public $productRepository;
    const BACKORDER_VALUE = 4;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Abbott\StockManagement\Helper\Data
     */
    private $helper;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Magento\Framework\App\Request\Http
     */

    protected $request;

    protected $orderRepository;
    protected $customerSession;
    protected $validateMetabolicOrderingProduct;

    /**
     * @param ProductRepositoryInterface $productRepository
     * ProductSearchPlugin constructor.
     * @param StoreManagerInterface $storeManager
     * @param \Abbott\StockManagement\Helper\Data $helper
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Abbott\StockManagement\Helper\Data $helper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Http $request,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        CustomerSession $customerSession,
        \Abbott\StockManagement\Helper\Data $validateMetabolicOrderingProduct
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->customerSession = $customerSession;
        $this->validateMetabolicOrderingProduct = $validateMetabolicOrderingProduct;
    }

    /**
     * @param Product $subject
     * @param $result
     * @return false|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterIsSalable(Product $subject, $result)
    {
        $isSubscription = $this->checkIsSubscription($subject);
        $product = $this->getProductData($subject->getId());
        if ($this->helper->getConfigValue() && $this->helper->checkStock($product) == self::BACKORDER_VALUE) {
            $threshold = $this->helper->getThreshold($product, $isSubscription);
            if ($product->getData()['quantity_and_stock_status']['is_in_stock'] &&
                $product->getData()['quantity_and_stock_status']['qty'] <= $threshold) {
                $result = false;
            }
            if ($product->getData()['order_on_call']) {
                $customerEmail = $this->customerSession->getCustomer()->getEmail();
                $productSku = $product->getSku();
                if ($this->validateMetabolicOrderingProduct->validateMetabolicOrderingProduct(
                    $customerEmail,
                    $productSku
                )
                ) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * @param Product $subject
     * @param $result
     * @return false|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetIsSalable(Product $subject, $result)
    {
        $isSubscription = $this->checkIsSubscription($subject);
        $product = $this->getProductData($subject->getId());
        if ($this->helper->getConfigValue() && $this->helper->checkStock($product) == self::BACKORDER_VALUE) {
            $threshold = $this->helper->getThreshold($product, $isSubscription);
            if ($product->getData()['quantity_and_stock_status']['is_in_stock'] &&
                $product->getData()['quantity_and_stock_status']['qty'] <= $threshold) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * get product details by id
     *
     * @param $productId
     * @return false|mixed
     */
    public function getProductData($productId)
    {
        return $this->productRepository->getById(
            $productId,
            false,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * check if order item is subscription product
     *
     * @param $productId
     * @return false|mixed
     */
    public function checkIsSubscription($subject)
    {
        $isSubscription = 0;
        if ($this->request->getParam('order_id') !== null) {
            $orderId = $this->request->getParam('order_id');
            $order = $this->orderRepository->get($orderId);
            foreach ($order->getAllVisibleItems() as $item) {
                if ($item->getProductId() == $subject->getId() &&
                    isset($item->getData()['product_options']['aw_sarp2_subscription_plan'])) {
                    $isSubscription = 1;
                }
            }
        }
        return $isSubscription;
    }
}
