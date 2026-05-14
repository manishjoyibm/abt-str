<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\StripePayments\Http\Client;

use Aheadworks\Sarp2\Gateway\StripePayments\Request\PaymentDataBuilder;
use Aheadworks\Sarp2\Gateway\StripePayments\Request\TransactionCaptureDataBuilder;

/**
 * Class TransactionCapture
 * @package Aheadworks\BamboraApac\Gateway\Http\Client
 */
class TransactionCapture extends AbstractTransaction
{
    /**
     * {@inheritdoc}
     */
    protected function process(array $data)
    {
        return  $this->adapter->capture(
            $data[TransactionCaptureDataBuilder::TRANSACTION_ID],
            $data[PaymentDataBuilder::AMOUNT]
        );
    }
}
