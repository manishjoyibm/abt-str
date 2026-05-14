<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\StripePayments\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class TransactionSaleDataBuilder
 * @package Aheadworks\Sarp2\Gateway\StripePayments\Request
 */
class TransactionSaleDataBuilder implements BuilderInterface
{
    /**
     * Request field name
     */
    const CAPTURE_METHOD = 'capture_method';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        return [
            self::CAPTURE_METHOD => 'automatic'
        ];
    }
}
