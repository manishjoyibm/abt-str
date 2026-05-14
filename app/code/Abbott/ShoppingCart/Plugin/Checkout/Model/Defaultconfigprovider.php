<?php

namespace Abbott\ShoppingCart\Plugin\Checkout\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Model\ProductFactory as Product;
use Abbott\Backorder\Helper\Data as dataHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Defaultconfigprovider
{
    /**
     * @var CheckoutSession
     */

    protected $checkoutSession;

    protected $product;

    /**
     * Constructor
     *
     * @param CheckoutSession $checkoutSession
     */
    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * Construct
     *
     * @param CheckoutSession $checkoutSession
     * @param dataHelper $dataHelper
     * @param Product $product
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        DataHelper $dataHelper,
        Product $product
    ) {
        $this->product = $product;
        $this->checkoutSession = $checkoutSession;
        $this->dataHelper = $dataHelper;
    }

    /**
     * AfterGetConfig
     *
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array $result
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        array $result
    ) {
        $productFactory = $this->product->create();
        $items = $result['totalsData']['items'];
        foreach ($items as $index => $item) {
            $quoteItem = $this->checkoutSession->getQuote()->getItemById($item['item_id']);
            if ($quoteItem->getProduct()->getData('size') && $this->dataHelper->getStatus('size')) {
                $result['quoteItemData'][$index]['size_attr'] = $quoteItem->getProduct()->getData('size');
                $attribute = $productFactory->getResource()->getAttribute('size');
                if ($attribute->usesSource()) {
                    $result['quoteItemData'][$index]['size_attr'] = $attribute->getSource()
                        ->getOptionText($quoteItem->getProduct()->getData('size'));
                }
            } else {
                $result['quoteItemData'][$index]['size_attr'] = '';
            }
            if ($quoteItem->getProduct()->getData('flavors') &&
                $this->dataHelper->getStatus('falvour')) {
                $result['quoteItemData'][$index]['flavour_attr'] = $quoteItem->getProduct()->getData('flavors');
                $attribute = $productFactory->getResource()->getAttribute('flavors');
                if ($attribute->usesSource()) {
                    $result['quoteItemData'][$index]['flavour_attr'] = $attribute->getSource()
                        ->getOptionText($quoteItem->getProduct()->getData('flavors'));
                }
            } else {
                $result['quoteItemData'][$index]['flavour_attr'] = '';
            }

            if ($this->dataHelper->getBackorderStatus($quoteItem->getProduct()) &&
                $this->dataHelper->getStatus('backorder')) {
                $result['quoteItemData'][$index]['backorder'] = 'Backorder';
            } else {
                $result['quoteItemData'][$index]['backorder'] = '';
            }
        }
        return $result;
    }
}
