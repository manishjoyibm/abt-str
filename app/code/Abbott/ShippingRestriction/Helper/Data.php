<?php

namespace Abbott\ShippingRestriction\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Directory\Model\Region;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Helper class for My Account:Contact Preferences
 */
class Data extends AbstractHelper
{
    public $storeManager;

    public $jsonHelper;
    public const XML_PATH_SHIPPING =
       'shipping_restrction/shipping_restrctions_group/is_enabled';
    public const XML_FREE_SHIPPING_STORE_LIST =
        'free_shipping_config/free_shipping_config_group/ship_list';
    public const XML_FEDEX_FREE_SHIPPING = 'carriers/fedex/free_method';
    const XML_FEDEX_FREE_THRESHOLD_AMOUNT = 'carriers/fedex/free_shipping_subtotal';
    const XML_PATH_FLAT_RATE_AMOUNT = 'shipping_restrction/shipping_restrctions_group/ground_shipping_amount';
    public const XML_PATH_FEDEXTRACKER = 'shipping_restrction/tracker/fedex_tracker_url';

    protected $productRepository;

    protected $cart;

    protected $region;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param Cart $cart
     * @param Region $region
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        Cart $cart,
        Region $region,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        TimezoneInterface $timezone
    ) {
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->region = $region;
        $this->jsonHelper = $jsonHelper;
        $this->timezone = $timezone;
        parent::__construct($context);
    }

    /**
     * Check isenabled
     *
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SHIPPING,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * GetFedExFreeShipping function
     *
     * @return false|string
     */
    public function getFedExFreeShipping()
    {
        $stores = $this->scopeConfig->getValue(
            self::XML_FREE_SHIPPING_STORE_LIST,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $fedExFreeShipping = $this->scopeConfig->getValue(
            self::XML_FEDEX_FREE_SHIPPING,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $storesArray = explode(",", $stores);
        if (in_array($this->getStoreId(), $storesArray)) {
            return strtolower($fedExFreeShipping);
        }
        return false;
    }

    /**
     * GetStoreId function
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * LoadProductBySKU function
     *
     * @param $sku
     * @return ProductInterface|null
     * @throws NoSuchEntityException
     */
    public function loadProductBySKU($sku = null)
    {
        if ($sku) {
            return $this->productRepository->get($sku);
        }
        return null;
    }

    /**
     * Validate Street Address
     *
     * @param $address
     * @return bool
     */
    public function validateStreet($address)
    {
        $addressline = $this->jsonHelper->jsonEncode($address);
        $addressline = strtolower($addressline);
        if (strpos($addressline, 'p.o. box') !== false ||
            strpos($addressline, 'po box') !== false ||
            strpos($addressline, 'pobox') !== false) {
            return true;
        }
        return false;
    }

    /**
     * GetFedExFreeThresholdAmount function
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getFedExFreeThresholdAmount()
    {
        return $this->scopeConfig->getValue(
            self::XML_FEDEX_FREE_THRESHOLD_AMOUNT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * GetFlatRateGroundShipRate function
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getFlatRateGroundShipRate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FLAT_RATE_AMOUNT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * GetFedexTrackerUrl function
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getFedexTrackerUrl()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FEDEXTRACKER,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * GetShipmentDate function
     *
     * @param $shipment
     * @return \DateTime
     * @throws \Exception
     */
    public function getShipmentDate($shipment)
    {
        $created = $shipment->getCreatedAt();
        //Convert to store timezone
        return $this->timezone->date(new \DateTime($created));
    }
}
