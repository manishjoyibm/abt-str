<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\PaymentData\Braintree\PayPalDetails;

use Magento\Framework\Intl\DateTimeFactory;

/**
 * Class ExpirationDate
 * @package Aheadworks\Sarp2\PaymentData\Braintree\PayPalDetails
 */
class ExpirationDate
{
    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @param DateTimeFactory $dateTimeFactory
     */
    public function __construct(DateTimeFactory $dateTimeFactory)
    {
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * Get formatted PayPal token expiration date
     *
     * @return string
     */
    public function getFormatted()
    {
        return $this->dateTimeFactory->create('now', new \DateTimeZone('UTC'))
            ->add(new \DateInterval('P1Y'))
            ->format('Y-m-d 00:00:00');
    }
}
