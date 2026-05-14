<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Observer\StripePaymentsSampler;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Aheadworks\Sarp2\Model\Adapter\StripePayments as StripePaymentsAdapter;
use Aheadworks\Sarp2\Model\Payment\SamplerInfoInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\StripePayments\CustomerId as StripeSamplerCustomerId;

/**
 * Class CustomerPaymentMethodAssignObserver
 *
 * @package Aheadworks\Sarp2\Observer\StripePaymentsSampler
 */
class CustomerPaymentMethodAssignObserver implements ObserverInterface
{
    /**
     * @var StripeSamplerCustomerId
     */
    private $stripeSamplerCustomerId;

    /**
     * @var StripePaymentsAdapter
     */
    private $stripePaymentsAdapter;

    /**
     * @param StripeSamplerCustomerId $stripeSamplerCustomerId
     * @param StripePaymentsAdapter $stripePaymentsAdapter
     */
    public function __construct(
        StripeSamplerCustomerId $stripeSamplerCustomerId,
        StripePaymentsAdapter $stripePaymentsAdapter
    ) {
        $this->stripeSamplerCustomerId = $stripeSamplerCustomerId;
        $this->stripePaymentsAdapter = $stripePaymentsAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        /** @var Event $event */
        $event = $observer->getEvent();
        /** @var SamplerInfoInterface $payment */
        $payment = $event->getData('payment');
        if ($payment) {
            $paymentToken = $payment->getAdditionalInformation('token');
            $customerId = $this->stripeSamplerCustomerId->resolve($payment->getProfile());
            $payment->setAdditionalInformation('customer_id', $customerId);
            $this->stripePaymentsAdapter->attachPaymentMethodToCustomer($customerId, $paymentToken);
        }

        return $this;
    }
}
