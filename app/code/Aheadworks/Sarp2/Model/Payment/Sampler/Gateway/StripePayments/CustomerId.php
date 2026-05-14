<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\StripePayments;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Model\Adapter\StripePayments as StripePaymentsAdapter;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class CustomerId
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\StripePayments
 */
class CustomerId
{
    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @var StripePaymentsAdapter
     */
    private $stripePaymentsAdapter;

    /**
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param StripePaymentsAdapter $stripePaymentsAdapter
     */
    public function __construct(
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        StripePaymentsAdapter $stripePaymentsAdapter
    ) {
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->stripePaymentsAdapter = $stripePaymentsAdapter;
    }

    /**
     * Resolve Stripe customer id value by subscription profile
     *
     * @param ProfileInterface $profile
     * @return string|null
     */
    public function resolve($profile)
    {
        try {
            $customerToken = $this->getSavedCustomerToken($profile);
            if (empty($customerToken)) {
                $currentCustomer = $this->stripePaymentsAdapter->getCurrentCustomer(
                    $profile->getCustomerId(),
                    $profile->getCustomerEmail(),
                    $profile->getCustomerFirstname(),
                    $profile->getCustomerLastname()
                );
                if ($currentCustomer) {
                    $customerToken = $currentCustomer->id;
                }
            }
        } catch (\Exception $exception) {
            $customerToken = null;
        }

        return $customerToken;
    }

    /**
     * Get saved customer token
     *
     * @param ProfileInterface $profile
     * @return string|null
     */
    private function getSavedCustomerToken($profile)
    {
        try {
            $paymentTokenId = $profile->getPaymentTokenId();
            $token = $this->paymentTokenRepository->get($paymentTokenId);
            $customerToken = $token->getDetails('customerToken');
        } catch (LocalizedException $e) {
            $customerToken = null;
        }

        return $customerToken;
    }
}
