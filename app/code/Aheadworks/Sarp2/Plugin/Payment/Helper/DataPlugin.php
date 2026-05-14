<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Plugin\Payment\Helper;

use Magento\Payment\Helper\Data;
use Aheadworks\Sarp2\Model\ThirdPartyModule\Manager as ThirdPartyModuleManager;

/**
 * Class DataPlugin
 * @package Aheadworks\Sarp2\Plugin\Payment\Helper
 */
class DataPlugin
{
    /**
     * @var ThirdPartyModuleManager
     */
    private $thirdPartyModuleManager;

    /**
     * @param ThirdPartyModuleManager $thirdPartyModuleManager
     */
    public function __construct(
        ThirdPartyModuleManager $thirdPartyModuleManager
    ) {
        $this->thirdPartyModuleManager = $thirdPartyModuleManager;
    }

    /**
     * Modify results of getPaymentMethods() call to remove not installed methods
     *
     * @param Data $subject
     * @param $result
     * @return array
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function afterGetPaymentMethods(Data $subject, $result)
    {
        if (!$this->thirdPartyModuleManager->isBamboraApacModuleEnabled()) {
            unset($result['aw_bambora_apac'], $result['aw_sarp_aw_bambora_apac_recurring']);
        }

        if (!$this->thirdPartyModuleManager->isStripeModuleEnabled()) {
            unset($result['stripe_payments'], $result['aw_sarp_stripe_payments_recurring']);
        }

        if (!$this->thirdPartyModuleManager->isBraintreeModuleEnabled()) {
            unset($result['braintree'], $result['aw_sarp_braintree_recurring']);
            unset($result['braintree_paypal'], $result['aw_sarp_braintree_paypal_recurring']);
        }

        return $result;
    }
}
