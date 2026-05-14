<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\PaymentData\StripePayments;

/**
 * Class ExpirationDate
 * @package Aheadworks\Sarp2\PaymentData\StripePayments
 */
class ExpirationDate
{
    /**
     * Get formatted credit card expiration date
     *
     * @param string $expMonth
     * @param string $expYear
     * @return string
     */
    public function getFormatted($expMonth, $expYear)
    {
        try {
            $time = sprintf('%s-%s-01 00:00:00', $expYear, $expMonth);
            $formattedDate = (new \DateTime($time))
                ->add(new \DateInterval('P1M'))
                ->format('Y-m-d 00:00:00');
        } catch (\Exception $e) {
            $formattedDate = '';
        }

        return $formattedDate;
    }
}
