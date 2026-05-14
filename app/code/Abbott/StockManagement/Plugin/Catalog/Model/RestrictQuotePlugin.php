<?php


namespace Abbott\StockManagement\Plugin\Catalog\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session as CustomerSession;
use Abbott\MetabolicOrdering\Helper\Data as MetabolicData;

class RestrictQuotePlugin
{
    const ERROR_MESSAGE = 'Product that you are trying to add is not available.';

    protected $customerSession;

    protected $validateMetabolicOrderingProduct;
    protected $metabolicData;


    public function __construct(
        CustomerSession $customerSession,
        \Abbott\StockManagement\Helper\Data $validateMetabolicOrderingProduct,
        MetabolicData $metabolicData
    ) {
        $this->customerSession = $customerSession;
        $this->validateMetabolicOrderingProduct = $validateMetabolicOrderingProduct;
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
            if ($this->validateMetabolicOrderingProduct->validateMetabolicOrderingProduct(
                $customerEmail,
                $productSku
            ) && ($metabolicQty > $quoteItemQty)) {
                return [$product, $request, $processMode];
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(self::ERROR_MESSAGE)
                );
            }


        }
    }
}
