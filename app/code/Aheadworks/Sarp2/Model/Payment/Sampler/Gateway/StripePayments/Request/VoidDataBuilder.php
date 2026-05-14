<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\StripePayments\Request;

use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class VoidDataBuilder
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\StripePayments\Request
 */
class VoidDataBuilder implements BuilderInterface
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

        $payment = $paymentDO->getPayment();

        $data = [
            self::TRANSACTION_ID => $payment->getLastTransactionId()
        ];
        return $data;
    }
}
