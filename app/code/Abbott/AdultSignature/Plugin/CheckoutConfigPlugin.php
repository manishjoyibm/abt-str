<?php
declare(strict_types=1);

namespace Abbott\AdultSignature\Plugin;

use Abbott\AdultSignature\Model\Config;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;

class CheckoutConfigPlugin
{
    private Config $config;
    private CheckoutSession $checkoutSession;
    private RegionCollectionFactory $regionCollectionFactory;

    public function __construct(
        Config $config,
        CheckoutSession $checkoutSession,
        RegionCollectionFactory $regionCollectionFactory
    ) {
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->regionCollectionFactory = $regionCollectionFactory;
    }

    public function afterGetConfig(
        CompositeConfigProvider $subject,
        $result
    ) {
        $quote = $this->checkoutSession->getQuote();
        $hasAdultProduct = false;
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

        $result['adultSignature'] = [
            'enabled'        => $this->config->isEnabled(),
            'restricStates'  => array_column($resolved, 'code'), // from global config (codes like "CA,NY")
            'popupMessage'   => $this->config->getPopMessage(),
            'hasAdultProduct'=> $hasAdultProduct                  // array of objects with id, code, name, country_id
        ];

        return $result;
    }

}