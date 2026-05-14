<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\BamboraApac\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class PurchaseTrnDataBuilder
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\BamboraApac\Request
 */
class PurchaseTrnDataBuilder implements BuilderInterface
{
    /**#@+
     * Transaction block names
     */
    const TRANSACTION_TYPE = 'TrnType';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        return [
            self::TRANSACTION_TYPE => 1 // Credit Card - Purchase
        ];
    }
}
