<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Observer;

use Aheadworks\Sarp2\Model\Payment\Checker\OfflinePayment;
use Aheadworks\Sarp2\Model\Quote\Management;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

/**
 * Class SaveOrderAfterSubmitObserver
 * @package Aheadworks\Sarp2\Observer
 */
class SaveOrderAfterSubmitObserver implements ObserverInterface
{
    /**
     * @var Management
     */
    private $quoteManagement;

    /**
     * @var OfflinePayment
     */
    private $offlinePaymentChecker;

    /**
     * @param Management $quoteManagement
     * @param OfflinePayment $offlinePaymentChecker
     */
    public function __construct(
        Management $quoteManagement,
        OfflinePayment $offlinePaymentChecker
    ) {
        $this->quoteManagement = $quoteManagement;
        $this->offlinePaymentChecker = $offlinePaymentChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /* @var $order Order */
        $order = $event->getData('order');
        /** @var Quote $quote */
        $quote = $event->getData('quote');

        $this->offlinePaymentChecker->check($quote->getPayment()->getMethod())
            ? $this->quoteManagement->createProfilesUsingPaymentMethod($quote, $quote->getPayment(), $order)
            : $this->quoteManagement->createProfiles($quote, $order);
    }
}
