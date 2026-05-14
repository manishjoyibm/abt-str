<?php
namespace Abbott\Checkout\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Abbott\Checkout\Helper\Data as CheckoutHelper;

/**
 * Class ConfigProvider
 *
 * Provides custom configuration data for Magento Checkout.
 * Specifically, adds a store-aware Contact Us URL to checkoutConfig.
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CheckoutHelper
     */
    public $checkoutHelper;

    /**
     * ConfigProvider constructor.
     *
     * @param CheckoutHelper $checkoutHelper Helper to retrieve AEM base URL.
     */
    public function __construct(CheckoutHelper $checkoutHelper)
    {
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * Retrieve checkout configuration.
     *
     * @return array<string, string> Returns an array with 'contactUrl' key.
     */
    public function getConfig(): array
    {
        // Generates a store-aware URL for the Contact Us page
        $url = $this->checkoutHelper->getAemUrl() . 'contact-us.html';

        return [
            'contactUrl' => $url
        ];
    }
}
