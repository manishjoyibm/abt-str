<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\Braintree\Request\PayPal;

use Aheadworks\Sarp2\Gateway\Braintree\SubjectReaderFactory;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class VaultDataBuilder
 * @package Aheadworks\Sarp2\Gateway\Braintree\Request\PayPal
 */
class VaultDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReaderFactory
     */
    private $subjectReaderFactory;

    /**
     * @param SubjectReaderFactory $subjectReaderFactory
     */
    public function __construct(SubjectReaderFactory $subjectReaderFactory)
    {
        $this->subjectReaderFactory = $subjectReaderFactory;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $subjectReader = $this->subjectReaderFactory->getInstance();
        $paymentDO = $subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();

        $isPaymentTokenEnabled = $payment->getAdditionalInformation('is_aw_sarp_payment_token_enabled');
        return $isPaymentTokenEnabled
            ? ['options' => ['storeInVaultOnSuccess' => true]]
            : [];
    }
}
