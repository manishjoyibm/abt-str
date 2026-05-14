<?php

namespace Abbott\CheckoutCaptcha\Plugin;

use Abbott\CheckoutCaptcha\Helper\Config;
use MSP\ReCaptcha\Model\LayoutSettings as ReCaptchaLayoutSettings;

/**
 * Provides Creditcard reCaptcha configuration.
 */
class LayoutSettings
{
    /**
     * @var Config
     */
    private $config;

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
     * AfterGetCaptchaSettings
     *
     * @param ReCaptchaLayoutSettings $subject
     * @param array $result
     * @return array
     */
    public function afterGetCaptchaSettings(
        ReCaptchaLayoutSettings $subject,
        array $result
    ) {
        $result['enabled']['creditcard'] = $this->config->isEnabledFrontendCreditCard();
        return $result;
    }
}
