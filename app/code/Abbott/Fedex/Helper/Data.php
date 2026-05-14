<?php

namespace Abbott\Fedex\Helper;

use Abbott\NewRelicReports\Model\NewRelicWrapper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    private const SMART_POST_FREE_SHIPPING_ENABLE = 'carriers/fedex/free_shipping_smart_post_enable';
    private const SMART_POST_FREE_SHIPPING_SUBTOTAL = 'carriers/fedex/free_shipping_smart_post_subtotal';
    private const SMART_POST_STATES = 'carriers/fedex/smart_post_states';
    private const TIMEOUT_FALLBACK = 'carriers/fedex/timeout_fallback_limit';
    private const FALLBACK_RATES = 'carriers/fedex/fallback_rates';
    private const EVENT_NAME = "fedex_timeout";

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var Json
     */
    protected Json $jsonSerializer;

    /**
     * @var NewRelicWrapper
     */
    protected NewRelicWrapper $newRelicWrapper;

    /**
     * Data constructor.
     * @param Context $context
     * @param Json $jsonSerializer
     * @param StoreManagerInterface $storeManager
     * @param NewRelicWrapper $newRelicWrapper
     */
    public function __construct(
        Context $context,
        Json $jsonSerializer,
        StoreManagerInterface $storeManager,
        NewRelicWrapper $newRelicWrapper
    ) {
        $this->storeManager = $storeManager;
        $this->jsonSerializer = $jsonSerializer;
        $this->newRelicWrapper = $newRelicWrapper;
        parent::__construct($context);
    }

    /**
     * Check if Smart Post FreeShipping Enabled
     *
     * @param int|null $websiteId
     * @return string
     * @throws LocalizedException
     */
    public function getSmartPostFreeShippingEnabled(int $websiteId = null): string
    {
        return $this->scopeConfig->getValue(
            self::SMART_POST_FREE_SHIPPING_ENABLE,
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getWebsite($websiteId)->getCode()
        );
    }

    /**
     * Get SmartPost Free Shipping Subtotal
     *
     * @param int|null $websiteId
     * @return string
     * @throws LocalizedException
     */
    public function getSmartPostFreeShippingSubtotal(int $websiteId = null): string
    {
        return $this->scopeConfig->getValue(
            self::SMART_POST_FREE_SHIPPING_SUBTOTAL,
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getWebsite($websiteId)->getCode()
        );
    }

    /**
     * Get SmartPost States
     *
     * @param int|null $websiteId
     * @return int[]
     * @throws LocalizedException
     */
    public function getSmartPostStates(int $websiteId = null): array
    {
        $states = $this->scopeConfig->getValue(
            self::SMART_POST_STATES,
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getWebsite($websiteId)->getCode()
        );
        return explode(",", $states);
    }

    /**
     * Get Fedex Fallback Timeout
     *
     * @param int|null $websiteId
     * @return int
     * @throws LocalizedException
     */
    public function getFedexFallbackTimeout(int $websiteId = null): int
    {
         return $this->scopeConfig->getValue(
             self::TIMEOUT_FALLBACK,
             ScopeInterface::SCOPE_WEBSITE,
             $this->storeManager->getWebsite($websiteId)->getCode()
         );
    }

    /**
     * Get Fedex Fallback Rates
     *
     * @param int|null $websiteId
     * @return array
     * @throws LocalizedException
     */
    public function getFedexFallbackRates(int $websiteId = null): array
    {
        $rates = $this->scopeConfig->getValue(
            self::FALLBACK_RATES,
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getWebsite($websiteId)->getCode()
        );
        return $this->jsonSerializer->unserialize($rates);
    }

    /**
     * Record FedEX time out event.
     * We record Quote ID in new relic to help us understand the level of impact when outage
     * happens
     *
     * @param int $quoteId
     * @throws NoSuchEntityException
     */
    public function generateTimeoutReport(int $quoteId): void
    {
        $data = [
            "store" => $this->storeManager->getStore()->getCode(),
            "quoteId" => $quoteId
        ];
        $this->newRelicWrapper->recordCustomEvent(self::EVENT_NAME, $data);
    }
}
