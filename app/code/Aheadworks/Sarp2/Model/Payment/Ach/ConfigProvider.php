<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Ach;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class ConfigProvider
 * @package Aheadworks\Sarp2\Model\Payment\Ach
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * Method Code
     */
    const METHOD_CODE = 'braintree_ach_direct_debit';

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::METHOD_CODE => [
                    'isActive' => false
                ]
            ]
        ];
    }
}
