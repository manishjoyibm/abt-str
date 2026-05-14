<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Notification;

use Aheadworks\Sarp2\Engine\Notification\DataResolver\ResolveSubject;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\Email\Template\PriceFormatter;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class DataResolver
 * @package Aheadworks\Sarp2\Engine\Notification
 */
class DataResolver
{
    /**
     * @var CoreDate
     */
    private $coreDate;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var PriceFormatter
     */
    private $priceFormatter;

    /**
     * @param CoreDate $coreDate
     * @param TimezoneInterface $timezone
     * @param PriceFormatter $priceFormatter
     */
    public function __construct(
        CoreDate $coreDate,
        TimezoneInterface $timezone,
        PriceFormatter $priceFormatter
    ) {
        $this->coreDate = $coreDate;
        $this->timezone = $timezone;
        $this->priceFormatter = $priceFormatter;
    }

    /**
     * Resolve notification data
     *
     * @param ResolveSubject $subject
     * @return array
     */
    public function resolve(ResolveSubject $subject)
    {
        $sourcePayment = $subject->getSourcePayment();
        $profile = $sourcePayment->getProfile();
        $currencyCode = $profile->getProfileCurrencyCode();
        $data = [
            'customerName' => $profile->getCustomerFullname(),
            'totalPaid' => $this->priceFormatter->format($sourcePayment->getTotalPaid(), $currencyCode),
            'totalScheduled' => $this->priceFormatter->format(
                $sourcePayment->getTotalScheduled(),
                $currencyCode
            ),
            'profileId' => $profile->getProfileId(),
            'orderId' => $sourcePayment->getOrderId(),
            'profileIncrementId' => $profile->getIncrementId()
        ];

        $nextPayments = $subject->getNextPayments();
        if (count($nextPayments)) {
            $nearestPayment = $this->getNearestPayment($nextPayments);
            $timezone = $this->timezone->getConfigTimezone(
                ScopeInterface::SCOPE_STORE,
                $profile->getStoreId()
            );

            $paymentDate = $this->timezone->formatDateTime(
                            new \DateTime($nearestPayment->getScheduledAt()),
                            \IntlDateFormatter::SHORT,
                            \IntlDateFormatter::NONE,
                            null,
                            $timezone
                            );
            $nextPaymentDateFormatted = date_format(date_create($paymentDate),"F d, Y");

            $data = array_merge(
                $data,
                [
                    'nextPaymentDate' => $nextPaymentDateFormatted,
                    'nextPaymentTotalAmount' => $this->priceFormatter->format(
                        $nearestPayment->getTotalScheduled(),
                        $currencyCode
                    )
                ]
            );
        }
        return $data;
    }

    /**
     * Get nearest payment
     *
     * @param PaymentInterface[] $nextPayments
     * @return PaymentInterface
     */
    private function getNearestPayment($nextPayments)
    {
        reset($nextPayments);
        /** @var PaymentInterface $nearestPayment */
        $nearestPayment = current($nextPayments);
        if (count($nextPayments) > 1) {

            /**
             * @param PaymentInterface $payment
             * @return void
             */
            $callback = function ($payment) use (&$nearestPayment) {
                if ($payment != $nearestPayment) {
                    $baseTm = $this->coreDate->gmtTimestamp($nearestPayment->getScheduledAt());
                    $currentTm = $this->coreDate->gmtTimestamp($payment->getScheduledAt());
                    if ($currentTm < $baseTm) {
                        $nearestPayment = $payment;
                    }
                }
            };
            array_walk($nextPayments, $callback);
        }
        return $nearestPayment;
    }
}
