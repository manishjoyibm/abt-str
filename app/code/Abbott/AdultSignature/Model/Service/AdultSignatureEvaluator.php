<?php
declare(strict_types=1);

namespace Abbott\AdultSignature\Model\Service;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Abbott\AdultSignature\Model\Config;

/**
 * Evaluates whether a quote requires adult signature based on products and destination state.
 *
 * @category  Abbott
 * @package   Abbott_AdultSignature
 */
class AdultSignatureEvaluator
{
    private const XPATH_ENABLED = 'abbott_adult_signature/general/enabled';
    private const XPATH_STATES  = 'abbott_adult_signature/general/restricted_states';

    /** @var Config */

    private Config $config;
    
    public function __construct(Config $config)
    {
         $this->config = $config;
    }

    /**
     * Determine whether the quote requires adult signature.
     *
     * @param CartInterface $quote The quote to evaluate
     * @return array{required: bool} Result array indicating requirement
     */
    public function evaluate(CartInterface $quote): array
    {
        $enabled =  $this->config->isEnabled();
        if (!$enabled) {
            return ['required' => false];
        }

        $address = $quote->getShippingAddress();
        if (!$address || !$address->getCountryId() || !$address->getRegionCode()) {
            // Not enough info yet
            return ['required' => false];
        }

        $state = strtoupper(trim((string)$address->getRegionCode()));

        $hasAdultProduct = false;
        $flaggedSkus = [];
        $allRegionIds = [];

        foreach ($quote->getAllVisibleItems() as $item) {
            
            $product = $item->getProduct();
            if (!$product) {
                continue;
            }
          

             // 1) Is this an adult-signature product?
            $isAdult = (int)$product->getData('abbott_requires_adult_signature') === 1;
            if ($isAdult) {
                $hasAdultProduct = true;
            }

            // 2) If you only want restrictions from adult products, gate here:
            if (!$isAdult) { continue; }

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
        // Resolve region IDs -> codes/names (single query)
        $resolved = $this->config->resolveRegions(array_values($allRegionIds));
        $restricted =  array_column($resolved, 'code');

        if ($hasAdultProduct && in_array($state, $restricted, true)) {
            return ['required' => true];
        }
        return ['required' => false];
    }
}
