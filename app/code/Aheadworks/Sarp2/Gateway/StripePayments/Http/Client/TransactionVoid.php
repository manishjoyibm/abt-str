<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\StripePayments\Http\Client;

use Aheadworks\Sarp2\Gateway\StripePayments\Request\TransactionVoidDataBuilder;

/**
 * Class TransactionVoid
 * @package Aheadworks\BamboraApac\Model\Api
 */
class TransactionVoid extends AbstractTransaction
{
    /**
     * {@inheritdoc}
     */
    protected function process(array $data)
    {
        return  $this->adapter->void(
            $data[TransactionVoidDataBuilder::TRANSACTION_ID]
        );
    }
}
