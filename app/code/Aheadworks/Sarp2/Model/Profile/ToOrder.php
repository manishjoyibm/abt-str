<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\Profile\Address\ToOrder as AddressToOrder;
use Aheadworks\Sarp2\Model\Profile\Address\ToOrderAddress as AddressToOrderAddress;
use Aheadworks\Sarp2\Model\Profile\Exception\CouldNotConvertException;
use Aheadworks\Sarp2\Model\Profile\Item\ToOrderItem as ItemToOrderItem;
use Aheadworks\Sarp2\Model\Sales\CopySelf;
use Aheadworks\Sarp2\Model\Sales\Item\Checker\IsVirtual;
use Aheadworks\Sarp2\Model\Sales\Order\IncrementIdProvider;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * Class ToOrder
 * @package Aheadworks\Sarp2\Model\Profile
 */
class ToOrder
{
    /**
     * @var AddressToOrder
     */
    private $profileAddressToOrder;

    /**
     * @var AddressToOrderAddress
     */
    private $profileAddressToOrderAddress;

    /**
     * @var ItemToOrderItem
     */
    private $profileItemToOrderItem;

    /**
     * @var ToOrderPayment
     */
    private $profileToOrderPayment;

    /**
     * @var CopySelf
     */
    private $selfCopyService;

    /**
     * @var IsVirtual
     */
    private $isVirtualChecker;

    /**
     * @var IncrementIdProvider
     */
    private $incrementIdProvider;

    /**
     * @param AddressToOrder $profileAddressToOrder
     * @param AddressToOrderAddress $profileAddressToOrderAddress
     * @param ItemToOrderItem $profileItemToOrderItem
     * @param ToOrderPayment $profileToOrderPayment
     * @param CopySelf $selfCopyService
     * @param IsVirtual $isVirtualChecker
     * @param IncrementIdProvider $incrementIdProvider
     */
    public function __construct(
        AddressToOrder $profileAddressToOrder,
        AddressToOrderAddress $profileAddressToOrderAddress,
        ItemToOrderItem $profileItemToOrderItem,
        ToOrderPayment $profileToOrderPayment,
        CopySelf $selfCopyService,
        IsVirtual $isVirtualChecker,
        IncrementIdProvider $incrementIdProvider
    ) {
        $this->profileAddressToOrder = $profileAddressToOrder;
        $this->profileAddressToOrderAddress = $profileAddressToOrderAddress;
        $this->profileItemToOrderItem = $profileItemToOrderItem;
        $this->profileToOrderPayment = $profileToOrderPayment;
        $this->selfCopyService = $selfCopyService;
        $this->isVirtualChecker = $isVirtualChecker;
        $this->incrementIdProvider = $incrementIdProvider;
    }

    /**
     * Convert profile to order
     *
     * @param ProfileInterface $profile
     * @param string $paymentPeriod
     * @return OrderInterface
     * @throws CouldNotConvertException
     */
    public function convert(ProfileInterface $profile, $paymentPeriod)
    {
        $profileBillingAddress = $profile->getBillingAddress();
        $orderBillingAddress = $this->profileAddressToOrderAddress->convert($profileBillingAddress);
        $orderAddresses = [$orderBillingAddress];

        if ($profile->getIsVirtual()
            || $paymentPeriod == PaymentInterface::PERIOD_INITIAL
        ) {
            $order = $this->profileAddressToOrder->convert($profileBillingAddress, $paymentPeriod);
        } else {
            $profileShippingAddress = $profile->getShippingAddress();
            $order = $this->profileAddressToOrder->convert($profileShippingAddress, $paymentPeriod);
            $orderShippingAddress = $this->profileAddressToOrderAddress->convert($profileShippingAddress);
            $order->setShippingAddress($orderShippingAddress);
            $orderAddresses[] = $orderShippingAddress;
        }
        $order->setBillingAddress($orderBillingAddress);
        $order->setAddresses($orderAddresses);
        $order->setPayment($this->profileToOrderPayment->convert($profile));

        /** @var OrderItem[] $orderItems */
        $orderItems = $this->convertItems($profile, $paymentPeriod);
        $order->setItems($orderItems);
        $order->setIsVirtual($this->isVirtualChecker->check($orderItems));
        $order->setIncrementId(
            $this->incrementIdProvider->getIncrementId($profile->getStoreId())
        );

        /** @var Order $order */
        $this->selfCopyService->copyByMap(
            $order,
            [
                [OrderInterface::ORDER_CURRENCY_CODE, OrderInterface::STORE_CURRENCY_CODE]
            ]
        );

        $this->validate($order);

        return $order;
    }

    /**
     * Convert profile items to order items
     *
     * @param ProfileInterface $profile
     * @param string $paymentPeriod
     * @return OrderItemInterface[]
     */
    private function convertItems(ProfileInterface $profile, $paymentPeriod)
    {
        $orderItems = [];
        foreach ($profile->getItems() as $profileItem) {
            $itemId = $profileItem->getItemId();
            if (!isset($orderItems[$itemId])) {
                $parentItemId = $profileItem->getParentItemId();
                if ($parentItemId && !isset($orderItems[$parentItemId])) {
                    $orderItems[$parentItemId] = $this->profileItemToOrderItem->convert(
                        $profileItem->getParentItem(),
                        $paymentPeriod,
                        ['parent_item' => null]
                    );
                }
                $parentItem = isset($orderItems[$parentItemId])
                    ? $orderItems[$parentItemId]
                    : null;
                $orderItems[$itemId] = $this->profileItemToOrderItem->convert(
                    $profileItem,
                    $paymentPeriod,
                    ['parent_item' => $parentItem]
                );
            }
        }
        return array_values($orderItems);
    }

    /**
     * Validate order entity
     *
     * @param OrderInterface|Order $order
     * @return void
     * @throws CouldNotConvertException
     */
    private function validate($order)
    {
        if (!$order->getIsVirtual() && !$order->getShippingMethod()) {
            throw new CouldNotConvertException('Unable to resolve shipping method.');
        }
    }
}
