<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Paypal\Plugin;

use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;
use PayPal\Braintree\Model\Paypal\Helper\QuoteUpdater as PayPalQuoteUpdater;
use Magento\Quote\Model\Quote;

/**
 * Class QuoteUpdater
 * @package Aheadworks\Sarp2\Model\Payment\Paypal\Plugin
 */
class QuoteUpdater
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
     * @param PayPalQuoteUpdater $subject
     * @param string $nonce
     * @param array $details
     * @param Quote $quote
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(
        PayPalQuoteUpdater $subject,
        $nonce,
        array $details,
        Quote $quote
    ) {
        $quote->getPayment()
            ->setAdditionalInformation(
                'is_aw_sarp_payment_token_enabled',
                $this->quoteChecker->check($quote)
            );
    }
}
