<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails;

use Magento\Payment\Model\CcConfigProvider;

/**
 * Class CreditCardIconResolver
 *
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails
 */
class CreditCardIconResolver
{
    /**
     * @var CcConfigProvider
     */
    private $creditCardIconsProvider;

    /**
     * @param CcConfigProvider $creditCardIconsProvider
     */
    public function __construct(
        CcConfigProvider $creditCardIconsProvider
    ) {
        $this->creditCardIconsProvider = $creditCardIconsProvider;
    }

    /**
     * Retrieve icon data array for specific credit card type
     *
     * @param string $creditCardType
     * @return array
     */
    public function getIconData($creditCardType)
    {
        if (isset($this->creditCardIconsProvider->getIcons()[$creditCardType])) {
            return $this->creditCardIconsProvider->getIcons()[$creditCardType];
        }

        return [
            'url' => '',
            'width' => 0,
            'height' => 0
        ];
    }
}
