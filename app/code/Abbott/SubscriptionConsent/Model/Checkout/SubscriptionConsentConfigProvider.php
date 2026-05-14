<?php
namespace Abbott\SubscriptionConsent\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;


class SubscriptionConsentConfigProvider implements ConfigProviderInterface
{
    private const XML_PATH_ENABLED = 'subscription_consent/general/enabled';
    private const XML_PATH_CONTENT = 'subscription_consent/general/content';
    private const XML_PATH_ERROR   = 'subscription_consent/general/error_message';

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var CheckoutSession */
    private $checkoutSession;

    /** @var LoggerInterface */
    private $logger;

     /** @var SubscriptionOptionRepositoryInterface */
    private SubscriptionOptionRepositoryInterface $subscriptionOptionRepository;


    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CheckoutSession $checkoutSession,
        LoggerInterface $logger,
        SubscriptionOptionRepositoryInterface $subscriptionOptionRepository

    ) {
        $this->scopeConfig     = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->logger          = $logger;
        $this->subscriptionOptionRepository = $subscriptionOptionRepository;

    }

    public function getConfig(): array
    {
        $quote   = $this->checkoutSession->getQuote();
        $storeId = $quote ? (int)$quote->getStoreId() : null;

        // Always return the key to avoid `undefined` on the frontend
        $config = [
            'abbott_subscription_consent' => [
                'enabled'       => false,
                'content'       => (string)$this->scopeConfig->getValue(self::XML_PATH_CONTENT, ScopeInterface::SCOPE_STORE, $storeId),
                'error_message' => (string)$this->scopeConfig->getValue(self::XML_PATH_ERROR,   ScopeInterface::SCOPE_STORE, $storeId),
                'product_detail' => ' '
            ]
        ];

        try {
            $enabledInConfig = (bool)$this->scopeConfig->isSetFlag(
                self::XML_PATH_ENABLED,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            if (!$enabledInConfig) {
                return $config; // feature off → return defaults with enabled=false
            }

            $detailsList = $this->getAllSubscriptionDetails($quote);

            if (!empty($detailsList)) {
                $config['abbott_subscription_consent']['enabled'] = true;
                $config['abbott_subscription_consent']['product_detail'] = $detailsList; // now it's an array
            }

            if ($isSubscription) {
                $config['abbott_subscription_consent']['enabled'] = true;
                $config['abbott_subscription_consent']['product_detail'] = $isSubscription;

            }
        } catch (\Exception $e) {
            $this->logger->warning(
                '[Abbott SubscriptionConsent] getConfig() failed: ' . $e->getMessage()
            );
            // fall through with defaults
        }

        return $config;
    }

    /**
     * Reads "is_abbott_subscription_item" from quote extension attributes, with fallbacks.
     */
    private function getAllSubscriptionDetails(?\Magento\Quote\Api\Data\CartInterface $quote): array
    {
        if (!$quote) {
            return [];
        }

        $result = [];

        foreach ((array)$quote->getAllVisibleItems() as $item) {

            // Find subscription option on item
            $option = $item->getOptionByCode('aw_sarp2_subscription_type');

            // Fallback if getOptionByCode doesn't work
            if (!$option) {
                foreach ((array)$item->getOptions() as $opt) {
                    if (is_object($opt) && $opt->getData('code') === 'aw_sarp2_subscription_type') {
                        $option = $opt;
                        break;
                    }
                }
            }

            if (!$option) {
                continue;
            }

            $subscriptionOptionId = (int)$option->getValue();
            if (!$subscriptionOptionId) {
                continue;
            }

            // Resolve Aheadworks subscription option -> plan
            $subscriptionOption = $this->subscriptionOptionRepository->get($subscriptionOptionId);
            $plan = $subscriptionOption->getPlan(); 

            $result[] = [
                'item_id'      => (int)$item->getId(),
                'product_id'   => (int)$item->getProductId(),
                'product_name' => (string)$item->getName(),
                'qty'          => (float)$item->getQty(),
                'price'        => (float)$item->getPrice(),
                'row_total'    => (float)$item->getRowTotal(),
                // build "X week" / "Every X weeks" here (your helper)
                'frequency'    => $this->buildFrequencyLabel($plan),
            ];
        }
        return $result;
    }


    /**
     * Get Subscription Id
     */
    private function buildFrequencyLabel($plan): string
    {
        // Try common method names
        $frequency = null;
        $period = null;

        if (is_object($plan)) {
            // some versions store frequency/period on plan or on definition object
            if (method_exists($plan, 'getBillingFrequency')) {
                $frequency = (int)$plan->getBillingFrequency();
            }
            if (method_exists($plan, 'getBillingPeriod')) {
                $period = (string)$plan->getBillingPeriod(); // week/month/day
            }

            // If plan has definition object:
            if ((!$frequency || !$period) && method_exists($plan, 'getDefinition')) {
                $def = $plan->getDefinition();
                if ($def) {
                    if (!$frequency && method_exists($def, 'getBillingFrequency')) {
                        $frequency = (int)$def->getBillingFrequency();
                    }
                    if (!$period && method_exists($def, 'getBillingPeriod')) {
                        $period = (string)$def->getBillingPeriod();
                    }
                }
            }
        }

        if ($frequency && $period) {
            $unit = strtolower($period);
            return sprintf('Every %d %s%s', $frequency, $unit, $frequency > 1 ? 's' : '');
        }

        // fallback: at least show definition id
        if (is_object($plan) && method_exists($plan, 'getDefinitionId')) {
            return 'Subscription (definition #' . $plan->getDefinitionId() . ')';
        }

        return 'Subscription';
    }

}   