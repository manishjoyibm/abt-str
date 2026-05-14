<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Observer\BraintreeRecurring\DataAssignObserver;
use Magento\Framework\DataObject\Copy;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterfaceFactory;

/**
 * Class ToOrderPayment
 * @package Aheadworks\Sarp2\Model\Profile
 */
class ToOrderPayment
{
    /**
     * @var OrderPaymentInterfaceFactory
     */
    private $orderPaymentFactory;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @param OrderPaymentInterfaceFactory $orderPaymentFactory
     * @param Copy $objectCopyService
     */
    public function __construct(
        OrderPaymentInterfaceFactory $orderPaymentFactory,
        Copy $objectCopyService
    ) {
        $this->orderPaymentFactory = $orderPaymentFactory;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * Convert profile to order payment
     *
     * @param ProfileInterface $profile
     * @return OrderPaymentInterface
     */
    public function convert(ProfileInterface $profile)
    {
        /** @var OrderPaymentInterface $orderPayment */
        $orderPayment = $this->orderPaymentFactory->create();
        $orderPayment->setMethod('aw_sarp_' . $profile->getPaymentMethod() . '_recurring')
            ->setAdditionalInformation(
                [DataAssignObserver::PAYMENT_TOKEN_ID => $profile->getPaymentTokenId()]
            );
        return $orderPayment;
    }
}
