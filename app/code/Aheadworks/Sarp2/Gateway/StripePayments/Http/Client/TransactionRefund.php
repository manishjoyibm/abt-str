<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\StripePayments\Http\Client;

use Aheadworks\Sarp2\Gateway\StripePayments\Request\PaymentDataBuilder;
use Aheadworks\Sarp2\Gateway\StripePayments\Request\TransactionRefundDataBuilder;

/**
 * Class TransactionRefund
 * @package Aheadworks\BamboraApac\Gateway\Http\Client
 */
class TransactionRefund extends AbstractTransaction
{
    /**
     * {@inheritdoc}
     */
    protected function process(array $data)
    {
        return  $this->adapter->refund(
            $data[TransactionRefundDataBuilder::TRANSACTION_ID],
            $data[PaymentDataBuilder::AMOUNT]
        );
    }
}
