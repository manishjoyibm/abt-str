<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Profile\Address;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Magento\Framework\DataObject\Copy;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;

/**
 * Class ToOrder
 * @package Aheadworks\Sarp2\Model\Profile\Address
 */
class ToOrder
{
    /**
     * @var OrderInterfaceFactory
     */
    private $orderFactory;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @param OrderInterfaceFactory $orderFactory
     * @param Copy $objectCopyService
     */
    public function __construct(
        OrderInterfaceFactory $orderFactory,
        Copy $objectCopyService
    ) {
        $this->orderFactory = $orderFactory;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * Convert profile address to order
     *
     * @param ProfileAddressInterface $profileAddress
     * @param string $paymentPeriod
     * @return OrderInterface
     */
    public function convert(ProfileAddressInterface $profileAddress, $paymentPeriod)
    {
        /** @var OrderInterface $order */
        $order = $this->orderFactory->create();
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp2_convert_profile_address',
            'to_order',
            $profileAddress,
            $order
        );
        $profile = $profileAddress->getProfile();
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp2_convert_profile',
            'to_order',
            $profile,
            $order
        );
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp2_convert_profile',
            'to_order_' . $paymentPeriod,
            $profile,
            $order
        );
        if ($paymentPeriod == PaymentInterface::PERIOD_INITIAL) {
            $order->setWeight(0);
        }
        // Braintree request builders workaround
        $order->setShippingAmount((float)$order->getShippingAmount());

        return $order;
    }
}
