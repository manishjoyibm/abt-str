<?php

namespace Abbott\ShippingRestriction\Plugin;

use Abbott\ShippingRestriction\Helper\Data;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartInterface;
use \Magento\Checkout\Model\Session as CheckoutSession;
use Abbott\MyAccount\Helper\Data as AccountHelper;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class AddExtensionAttributesToQuote
{
    public $shippRestrictionHelper;

    public $logger;

    /**
     * @var CartExtensionFactory
     */
    private $cartExtensionFactory;

    protected $checkoutSession;

    protected $storeManager;

    /**
     * Construct function
     *
     * @param CartExtensionFactory $cartExtensionFactory
     * @param CheckoutSession $checkoutSession
     * @param Data $shippRestrictionHelper
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        CartExtensionFactory $cartExtensionFactory,
        CheckoutSession $checkoutSession,
        Data $shippRestrictionHelper,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->cartExtensionFactory = $cartExtensionFactory;
        $this->checkoutSession = $checkoutSession;
        $this->shippRestrictionHelper = $shippRestrictionHelper;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * AfterGet function
     *
     * @param CartRepositoryInterface $subject
     * @param $result
     * @return CartInterface|mixed
     */
    public function afterGet(CartRepositoryInterface $subject, $result)
    {
        try {
            $quoteItems = $result->getAllVisibleItems();
            $isHazardous = null;
            $isAbbottSubscribeItem = null;
            foreach ($quoteItems as $item) {
                $productSku = $item->getSku();
                if ($this->storeManager->getStore()->getId() == 1 ||
                    $this->storeManager->getStore()->getCode() ==
                    AccountHelper::NEW_SIM_STORE_CODE) {
                    $isAbbottSubscribeItem = $this->checkSubscriptionItem($item);
                    if ($isAbbottSubscribeItem) {
                        break;
                    }
                }
                $productData = $this->shippRestrictionHelper->loadProductBySKU($productSku);
                if ($productData->getData("is_hazardous")) {
                    $isHazardous = 1;
                    break;
                }
            }
            if ($result instanceof CartInterface) {
                $extensionAttributes = $result->getExtensionAttributes();
                $extensionAttributes->setData('is_item_hazard', $isHazardous);
                $extensionAttributes->setData('is_abbott_subscription_item', $isAbbottSubscribeItem);
                $result->setExtensionAttributes($extensionAttributes);
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
        return $result;
    }

    /**
     * CheckSubscriptionItem function
     *
     * @param $item
     * @return true|null
     */
    public function checkSubscriptionItem($item)
    {
        foreach ($item->getOptions() as $option) {
            if (isset($option->getData()['code']) && $option->getData()['code'] == "aw_sarp2_subscription_type") {
                return true;
            }
        }
        return null;
    }
}
