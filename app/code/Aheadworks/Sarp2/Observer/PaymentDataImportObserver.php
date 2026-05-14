<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Observer;

use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Payment;

/**
 * Class PaymentDataImportObserver
 * @package Aheadworks\Sarp2\Observer
 */
class PaymentDataImportObserver implements ObserverInterface
{
    /**
     * @var HasSubscriptions
     */
    private $quoteChecker;

    /**
     * @param HasSubscriptions $quoteChecker
     */
    public function __construct(HasSubscriptions $quoteChecker)
    {
        $this->quoteChecker = $quoteChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /* @var $payment Payment */
        $payment = $event->getData('payment');
        /** @var DataObject $input */
        $input = $event->getData('input');
        $quote = $payment->getQuote();
        $additionalData = $input->getAdditionalData() ? : [];
        $additionalData['is_aw_sarp_payment_token_enabled'] = $this->quoteChecker->check($quote);
        $input->setAdditionalData($additionalData);
    }
}
