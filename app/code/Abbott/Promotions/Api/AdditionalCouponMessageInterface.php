<?php

namespace Abbott\Promotions\Api;

use Abbott\Promotions\Api\Data\MessageDataInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Coupon management service interface.
 *
 * @api
 * @since 100.0.2
 */
interface AdditionalCouponMessageInterface
{
    /**
     * Adds a coupon by code to a specified cart.
     *
     * @param int $cartId The cart ID.
     * @param string $couponCode The coupon code data.
     * @return MessageDataInterface
     * @throws NoSuchEntityException The specified cart does not exist.
     * @throws CouldNotSaveException The specified coupon could not be added.
     */
    public function setCoupon($cartId, $couponCode);
}
