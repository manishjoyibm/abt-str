<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\StripePayments\Response;

/**
 * Class RefundHandler
 * @package Aheadworks\Sarp2\Gateway\StripePayments\Response
 */
class RefundHandler extends TransactionIdHandler
{
    /**
     * {@inheritdoc}
     */
    protected function setTransactionId($payment, $transactionResponse)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    protected function shouldCloseTransaction()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function shouldCloseParentTransaction($payment)
    {
        return !(bool)$payment->getCreditmemo()->getInvoice()->canRefund();
    }
}
