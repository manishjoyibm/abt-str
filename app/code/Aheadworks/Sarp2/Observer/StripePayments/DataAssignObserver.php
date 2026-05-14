<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Observer\StripePayments;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Class DataAssignObserver
 * @package Aheadworks\Sarp2\Observer\StripePayments
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (is_array($additionalData)) {
            $paymentInfo = $this->readPaymentModelArgument($observer);
            if (isset($additionalData['is_aw_sarp_payment_token_enabled'])) {
                $paymentInfo->setAdditionalInformation(
                    'is_aw_sarp_payment_token_enabled',
                    $additionalData['is_aw_sarp_payment_token_enabled']
                );
                if ($additionalData['is_aw_sarp_payment_token_enabled']) {
                    $additionalData['cc_save'] = true;
                    $data->setAdditionalData($additionalData);
                }
            }
        }
    }
}
