<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\StripePayments\Request;

use Aheadworks\Sarp2\Gateway\StripePayments\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class TransactionVoidDataBuilder
 * @package Aheadworks\Sarp2\Gateway\StripePayments\Request
 */
class TransactionVoidDataBuilder implements BuilderInterface
{
    /**
     * Transaction id (intent id)
     */
    const TRANSACTION_ID = 'id';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        return [
            self::TRANSACTION_ID => $payment->getLastTransId()
        ];
    }
}
