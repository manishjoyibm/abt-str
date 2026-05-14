<?php


namespace Abbott\StockManagement\Plugin\Catalog\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Abbott\MetabolicOrdering\Helper\Data as MetabolicData;

class QuotePlugin
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    public $productRepository;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;
    const BACKORDER_VALUE = 4;
    const ERROR_MESSAGE = 'Product that you are trying to add is not available.';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Abbott\StockManagement\Helper\Data
     */
    private $helper;

    protected $customerSession;

    protected $metabolicData;

    public function __construct(
        StoreManagerInterface $storeManager,
        \Abbott\StockManagement\Helper\Data $helper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CustomerSession $customerSession,
        MetabolicData $metabolicData
    ) {

        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->metabolicData = $metabolicData;
    }

    /**
     * @param \Magento\Quote\Model\Quote $subject
     * @param \Magento\Catalog\Model\Product $product
     * @param null $request
     * @param string $processMode
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddProduct(
        \Magento\Quote\Model\Quote $subject,
        \Magento\Catalog\Model\Product $product,
        $request = null,
        $processMode = \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL
    ) {
        if ($product->getData()['order_on_call']) {
            $customerEmail = $this->customerSession->getCustomer()->getEmail();
            $productSku = $product->getSku();
            if ($customerEmail) {
                $data['sku'] = $productSku;
                $data['customer_email'] = $customerEmail;
                $metabolicDataResult = $this->metabolicData->ifExistingRecord($data);
                $metabolicQty = $metabolicDataResult['qty'];
                $quoteItems = $subject->getAllItems();
                $productId = $product->getId();
                $quoteItemQty = 0;
                foreach ($quoteItems as $quoteItem) {
                    if ($quoteItem->getProductId() == $productId) {
                        $quoteItemQty = $quoteItem->getQty();
                    }
                }
                if ($metabolicQty < $quoteItemQty) {
                     throw new \Magento\Framework\Exception\LocalizedException(
                         __(self::ERROR_MESSAGE)
                     );
                }
            }
        }

        if ($this->helper->getConfigValue() && $this->helper->checkStock($product) == self::BACKORDER_VALUE) {
            $threshold = $product->getThreshold();
            if ($product->getData()['quantity_and_stock_status']['is_in_stock'] &&
                $product->getData()['quantity_and_stock_status']['qty'] <= $threshold) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(self::ERROR_MESSAGE)
                );
            }
        }
        return [$product, $request, $processMode];
    }
}
