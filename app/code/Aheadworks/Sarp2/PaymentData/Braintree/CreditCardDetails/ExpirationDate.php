<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\PaymentData\Braintree\CreditCardDetails;

use Braintree\CreditCard;

/**
 * Class ExpirationDate
 * @package Aheadworks\Sarp2\PaymentData\Braintree\CreditCardDetails
 */
class ExpirationDate
{
    /**
     * Get formatted credit card expiration date
     *
     * @param CreditCard $creditCard
     * @return string
     */
    public function getFormatted($creditCard)
    {
        $time = sprintf('%s-%s-01 00:00:00', $creditCard->expirationYear, $creditCard->expirationMonth);
        return (new \DateTime($time))
            ->add(new \DateInterval('P1M'))
            ->format('Y-m-d 00:00:00');
    }
}
