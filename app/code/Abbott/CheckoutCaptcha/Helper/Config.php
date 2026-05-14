<?php

namespace Abbott\CheckoutCaptcha\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Configuration
 */
class Config
{
    public const XML_PATH_ENABLED_FRONTEND_CREDITCARD = 'msp_securitysuite_recaptcha/frontend/enabled_creditcard';
    public const XML_PATH_FAILED_PAYMENT_COUNT = 'msp_securitysuite_recaptcha/frontend/failed_payment_count';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Construct function
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * IsEnabledFrontendCreditCard
     *
     * @return bool
     */
    public function isEnabledFrontendCreditCard()
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED_FRONTEND_CREDITCARD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * GetPaymentFailedCount
     *
     * @return mixed
     */
    public function getPaymentFailedCount()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FAILED_PAYMENT_COUNT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
