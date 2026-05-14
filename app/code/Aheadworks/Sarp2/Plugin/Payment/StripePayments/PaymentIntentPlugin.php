<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Plugin\Payment\StripePayments;

use Aheadworks\Sarp2\Gateway\StripePayments\TokenAssigner;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use StripeIntegration\Payments\Model\PaymentIntent;
use Stripe\PaymentIntent as StripePaymentIntent;

/**
 * Class PaymentIntentPlugin
 * @package Aheadworks\Sarp2\Plugin\Payment\StripePayments
 */
class PaymentIntentPlugin
{
    /**
     * @var TokenAssigner
     */
    private $tokenAssigner;

    /**
     * @param TokenAssigner $tokenAssigner
     */
    public function __construct(
        TokenAssigner $tokenAssigner
    ) {
        $this->tokenAssigner = $tokenAssigner;
    }

    /**
     * Assign token to payment
     *
     * @param PaymentIntent $subject
     * @param StripePaymentIntent $result
     * @param OrderInterface $order
     * @return StripePaymentIntent
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function afterConfirmAndAssociateWithOrder($subject, $result, $order)
    {
        /** @var OrderPaymentInterface|null $payment */
        $payment = $order->getPayment();

        if ($payment
            && $payment->getAdditionalInformation('is_aw_sarp_payment_token_enabled')
        ) {
            $this->tokenAssigner->assignByPaymentIntent($payment, $result);
        }

        return $result;
    }
}
