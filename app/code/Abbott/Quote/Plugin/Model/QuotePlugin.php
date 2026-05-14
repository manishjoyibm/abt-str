<?php


namespace Abbott\Quote\Plugin\Model;

use Abbott\Quote\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\Session as CustomerSession;
use Abbott\MetabolicOrdering\Helper\Data as MetabolicData;
use Magento\Quote\Model\Quote;

class QuotePlugin
{
    public const ERROR_MESSAGE = 'Product that you are trying to add is not available.';

    /**
     * @var Data
     */
    private Data $helper;

    /**
     * @var CustomerSession
     */
    protected CustomerSession $customerSession;

    /**
     * @var MetabolicData
     */
    protected MetabolicData $metabolicData;

    /**
     * QuotePlugin constructor.
     * @param Data $helper
     * @param CustomerSession $customerSession
     * @param MetabolicData $metabolicData
     */
    public function __construct(
        Data            $helper,
        CustomerSession $customerSession,
        MetabolicData   $metabolicData
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->metabolicData = $metabolicData;
    }

    /**
     * Before Add Product
     *
     * @param Quote $subject
     * @param Product $product
     * @param $request
     * @param string $processMode
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddProduct(
        Quote $subject,
        Product $product,
        $request = null,
        $processMode = AbstractType::PROCESS_MODE_FULL
    ): array {
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
                     throw new LocalizedException(
                         __(self::ERROR_MESSAGE)
                     );
                }
            }
        }
        if ($this->helper->isQtyLimitEnabled()) {
            $qtyAllowed = [];

            if ($minQty = preg_replace('/\D/', '', $product->getData('cans_y') ?? "")) {
                $qtyAllowed[] = $minQty;
            }
            if ($maxQty = preg_replace('/\D/', '', $product->getData('cans_x') ?? "")) {
                $qtyAllowed[] = $maxQty;
            }
            if (!empty($qtyAllowed) && !in_array($request->getQty(), $qtyAllowed)) {
                throw new LocalizedException(
                    __('We found an invalid request for adding product to quote.')
                );
            }
        }

        return [$product, $request, $processMode];
    }
}
