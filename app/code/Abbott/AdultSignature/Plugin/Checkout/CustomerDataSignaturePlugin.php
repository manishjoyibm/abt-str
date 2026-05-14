<?php
declare(strict_types=1);

namespace Abbott\AdultSignature\Plugin\Checkout;

use Magento\Checkout\CustomerData\Cart as CoreCart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Directory\Model\RegionFactory;
use Abbott\AdultSignature\Model\Config;

/**
 * Plugin to append adult signature flags to the checkout customer-data cart section.
 * Now includes state restriction condition.
 */
class CustomerDataSignaturePlugin
{
    private CheckoutSession $checkoutSession;
    private ScopeConfigInterface $scopeConfig;
    private RegionFactory $regionFactory;
    private Config $config;

    public function __construct(
        CheckoutSession $checkoutSession,
        ScopeConfigInterface $scopeConfig,
        RegionFactory $regionFactory,
        Config $config
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->regionFactory = $regionFactory;
        $this->config = $config;
    }

    /**
     * Append adult signature flags ONLY IF:
     * 1) Product requires adult signature
     * 2) Shipping state is in restricted list
     *
     * @param CoreCart $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(CoreCart $subject, array $result): array
    {
        $quote = $this->checkoutSession->getQuote();
        if (!$quote) {
            return $result;
        }

        // -------- STEP 1: Determine if any product requires adult signature -------
        $containsFlagged = false;
          // We’ll collect region IDs from *flagged* products (or all items if that’s desired).
        // If business rule is "only states from products that require signature", keep as-is.
        $allRegionIds = [];

        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();

            // 1) Is this an adult-signature product?
            $isAdult = (int)$product->getData('abbott_requires_adult_signature') === 1;
            if ($isAdult) {
                $containsFlagged = true;
                break;
            }

            // 2) If you only want restrictions from adult products, gate here:
            // if (!$isAdult) { continue; }

            // 3) Read shipping restriction attribute (multi-select often returns CSV string)
            $raw = $product->getData('abbott_shipping_state_adult_signature');
            if ($raw === null || $raw === '' || $raw === false) {
                continue;
            }
            
            $ids = is_array($raw) ? $raw : explode(',', (string)$raw);

            foreach ($ids as $id) {
                $id = (int) trim($id);
                if ($id > 0) {
                    $allRegionIds[$id] = $id;
                }
            }
        }

        if (!$containsFlagged) {
            // No flagged product → no popup ever
            $result['adult_signature'] = [
                'required' => false,
                'accepted' => false
            ];
            return $result;
        }

        // -------- STEP 2: Evaluate shipping state --------
        $address = $quote->getShippingAddress();
        if (!$address) {
            $result['adult_signature'] = [
                'required' => false,
                'accepted' => false
            ];
            return $result;
        }

        // Resolve state code (regionCode or via regionId)
        $state = strtoupper((string)$address->getRegionCode());
        if ($state === '' && $address->getRegionId()) {
            $region = $this->regionFactory->create()->load((int)$address->getRegionId());
            $state = strtoupper((string)$region->getCode());
        }

        if ($state === '') {
            $result['adult_signature'] = [
                'required' => false,
                'accepted' => false
            ];
            return $result;
        }

        // -------- STEP 3: Load restricted states from config --------
        // Resolve region IDs -> codes/names (single query)
        $resolved = $this->config->resolveRegions(array_values($allRegionIds));
        $restricted =  array_column($resolved, 'code');

        // If no restricted states → requirement OFF
        if (empty($restricted)) {
            $result['adult_signature'] = [
                'required' => false,
                'accepted' => false
            ];
            return $result;
        }

        // -------- STEP 4: Final decision --------
        $required = $containsFlagged && in_array($state, $restricted, true);

        $result['adult_signature'] = [
            'required' => $required,
            'accepted' => (bool)$quote->getData('adult_signature_accepted')
        ];

        return $result;
    }
}