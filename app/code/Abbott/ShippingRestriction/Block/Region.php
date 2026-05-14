<?php
namespace Abbott\ShippingRestriction\Block;

use Abbott\ShippingRestriction\Helper\Data;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class Region extends Template
{
    public $shippRestrictionHelper;

    protected $cart;

    protected $region;

    protected $storeManager;

    /**
     * Construct function
     *
     * @param Template\Context $context
     * @param Cart $cart
     * @param \Magento\Directory\Model\Region $region
     * @param Data $shippRestrictionHelper
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Cart $cart,
        \Magento\Directory\Model\Region $region,
        Data $shippRestrictionHelper,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->cart = $cart;
        $this->region = $region;
        $this->shippRestrictionHelper = $shippRestrictionHelper;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * GetRegionNameById function
     *
     * @param $region_id
     * @return string|null
     */
    public function getRegionNameById($region_id)
    {
        $regionData = $this->region->load($region_id);
        if ($regionData) {
            return $regionData->getName();
        }

        return null;
    }

    /**
     * LoadQuote  Items
     *
     * @return array
     */
    public function loadQuoteItems()
    {
        return $this->cart->getQuote()->getAllItems();
    }

    /**
     * Get Product By Sku
     *
     * @param $sku
     * @return ProductInterface|null
     */
    public function getProductBySku($sku)
    {
        return $this->shippRestrictionHelper->loadProductBySKU($sku);
    }

    /**
     * GetStore Id
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}
