<?php

declare(strict_types=1);

namespace Abbott\CheckoutCaptcha\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Abbott\CheckoutCaptcha\Helper\Config;

class CheckoutConfigProvider implements ConfigProviderInterface
{

    public $config;

    /**
     * Construct function
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * GetConfig function
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment_failed_count' => $this->config->getPaymentFailedCount(),
            'captcha_enabled' => $this->config->isEnabledFrontendCreditCard()
        ];
    }
}
