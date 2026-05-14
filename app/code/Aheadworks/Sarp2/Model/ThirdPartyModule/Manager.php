<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\ThirdPartyModule;

use Magento\Framework\Module\ModuleListInterface;

/**
 * Class Manager
 *
 * @package Aheadworks\Sarp2\Model\ThirdPartyModule
 */
class Manager
{
    /**
     * Aheadworks BamboraApac module name
     */
    const BAMBORA_MODULE_NAME = 'Aheadworks_BamboraApac';

    /**
     * Stripe payment module name
     */
    const STRIPE_MODULE_NAME = 'StripeIntegration_Payments';

    /**
     * Authorizenet Cardinal module name
     */
    const AUTHORIZENET_CARDINAL_MODULE_NAME = 'Magento_AuthorizenetCardinal';

    /**
     * Braintree module name
     */
    const BRAINTREE_MODULE_NAME = 'PayPal_Braintree';

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        ModuleListInterface $moduleList
    ) {
        $this->moduleList = $moduleList;
    }

    /**
     * Check if Aheadworks BamboraApac module enabled
     *
     * @return bool
     */
    public function isBamboraApacModuleEnabled()
    {
        return $this->moduleList->has(self::BAMBORA_MODULE_NAME);
    }

    /**
     * Check if Stripe payments module enabled
     *
     * @return bool
     */
    public function isStripeModuleEnabled()
    {
        return $this->moduleList->has(self::STRIPE_MODULE_NAME);
    }

    /**
     * Check if Authorizenet Cardinal module enabled
     *
     * @return bool
     */
    public function isAuthorizenetCardinalModuleEnabled()
    {
        return $this->moduleList->has(self::AUTHORIZENET_CARDINAL_MODULE_NAME);
    }

    /**
     * Check if Braintree module enabled
     *
     * @return bool
     */
    public function isBraintreeModuleEnabled()
    {
        return $this->moduleList->has(self::BRAINTREE_MODULE_NAME);
    }

    /**
     * Check if Braintree Ach payment exist
     *
     * @return bool
     */
    public function isBraintreeAchPaymentExist()
    {
        return class_exists('PayPal\Braintree\Model\Ach\Ui\ConfigProvider');
    }
}
