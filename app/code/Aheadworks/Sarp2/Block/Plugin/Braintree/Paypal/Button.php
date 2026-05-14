<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Plugin\Braintree\Paypal;

use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;
use PayPal\Braintree\Block\Paypal\Button as ButtonBlock;
use Magento\Checkout\Model\Session;

/**
 * Class Button
 * @package Aheadworks\Sarp2\Block\Plugin\Braintree\Paypal
 */
class Button
{
    /**
     * @var HasSubscriptions
     */
    private $quoteChecker;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param HasSubscriptions $quoteChecker
     * @param Session $checkoutSession
     */
    public function __construct(
        HasSubscriptions $quoteChecker,
        Session $checkoutSession
    ) {
        $this->quoteChecker = $quoteChecker;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param ButtonBlock $subject
     * @param bool $isActive
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsActive(ButtonBlock $subject, $isActive)
    {
        return !$isActive ? : !$this->quoteChecker->check($this->checkoutSession->getQuote());
    }
}
