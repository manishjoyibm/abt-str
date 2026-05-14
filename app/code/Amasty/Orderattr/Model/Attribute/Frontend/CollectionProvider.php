<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Attribute\Frontend;

use Amasty\Orderattr\Api\Data\CheckoutAttributeInterface;
use Amasty\Orderattr\Model\Config\Source\CheckoutStep;
use Amasty\Orderattr\Model\QuoteProducts;
use Amasty\Orderattr\Model\ResourceModel\Attribute\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;

class CollectionProvider
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $shippingAttributes = [];

    /**
     * @var array
     */
    private $paymentAttributes = [];

    /**
     * @var \Amasty\Orderattr\Model\ResourceModel\Attribute\Collection
     */
    private $collection;

    /**
     * @var QuoteProducts
     */
    private $quoteProducts;

    public function __construct(
        StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory,
        QuoteProducts $quoteProducts = null //todo: move to not optional
    ) {
        $this->storeManager = $storeManager;
        $this->collection = $collectionFactory->create();
        $this->quoteProducts = $quoteProducts ?? ObjectManager::getInstance()->create(QuoteProducts::class);
        $this->collection->setOrder(CheckoutAttributeInterface::SORTING_ORDER, 'ASC');
        $this->collection->addFieldToFilter(CheckoutAttributeInterface::IS_VISIBLE_ON_FRONT, 1);
    }

    /**
     * @return \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface[]
     */
    public function getAttributes(?Quote $quote = null)
    {
        $this->collection->addStoreFilter($this->storeManager->getStore()->getId());
        $this->collection->addConditionsFilter($this->quoteProducts->getProductIds($quote));

        return $this->collection->getItems();
    }

    /**
     * @return \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface[]
     */
    public function getShippingAttributes()
    {
        if (!$this->shippingAttributes) {
            $this->shippingAttributes = $this->getAttributesForStep(CheckoutStep::SHIPPING_STEP);
        }

        return $this->shippingAttributes;
    }

    /**
     * @return \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface[]
     */
    public function getPaymentAttributes()
    {
        if (!$this->paymentAttributes) {
            $this->paymentAttributes = $this->getAttributesForStep(CheckoutStep::PAYMENT_STEP);
        }

        return $this->paymentAttributes;
    }

    /**
     * @param $checkoutStep
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface[]
     */
    public function getAttributesForStep($checkoutStep)
    {
        $result = [];

        foreach ($this->getAttributes() as $frontendAttribute) {
            if ((int)$frontendAttribute->getCheckoutStep() === $checkoutStep) {
                $result[] = $frontendAttribute;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAttributeCodes()
    {
        return $this->collection->getColumnValues('attribute_code');
    }
}
