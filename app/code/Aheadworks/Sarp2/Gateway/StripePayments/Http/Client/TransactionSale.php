<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\StripePayments\Http\Client;

/**
 * Class TransactionSale
 * @package Aheadworks\BamboraApac\Model\Api
 */
class TransactionSale extends AbstractTransaction
{
    /**
     * {@inheritdoc}
     */
    protected function process(array $data)
    {
        return $this->adapter->singlePayment($data);
    }
}
