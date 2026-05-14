<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Customer;

use Aheadworks\Sarp2\Model\Payment\Token\Finder as PaymentTokenFinder;
use Aheadworks\Sarp2\Model\Profile\Finder as ProfileFinder;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class StripeCardsChecker
 * @package Aheadworks\Sarp2\Block\Customer
 */
class StripeCardsChecker extends Template
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var ProfileFinder
     */
    private $profileFinder;

    /**
     * @var PaymentTokenFinder
     */
    private $paymentTokenFinder;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param ProfileFinder $profileFinder
     * @param PaymentTokenFinder $paymentTokenFinder
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        ProfileFinder $profileFinder,
        PaymentTokenFinder $paymentTokenFinder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->profileFinder = $profileFinder;
        $this->paymentTokenFinder = $paymentTokenFinder;
    }

    /**
     * Get active subcriptions tokens
     *
     * @return array
     */
    public function getActiveSubscriptionTokens()
    {
        $activeTokens = [];
        $customerId = $this->customerSession->getCustomerId();
        if ($customerId) {
            $profiles = $this->profileFinder->getActiveProfilesByCustomerId($customerId);
            $tokenIds = [];
            foreach ($profiles as $profile) {
                $tokenIds[] = $profile->getPaymentTokenId();
            }
            $paymentTokens = $this->paymentTokenFinder->findExistingByIds($tokenIds, 'stripe_payments');
            foreach ($paymentTokens as $paymentToken) {
                $activeTokens[] = $paymentToken->getTokenValue();
            }
        }

        return $activeTokens;
    }
}
