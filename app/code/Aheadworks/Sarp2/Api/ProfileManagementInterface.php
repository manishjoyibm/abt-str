<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Api;

/**
 * Interface ProfileManagementInterface
 * @package Aheadworks\Sarp2\Api
 */
interface ProfileManagementInterface
{
    /**
     * Perform profile scheduling
     *
     * @param \Aheadworks\Sarp2\Api\Data\ProfileInterface[] $profiles
     * @return void
     * @throws \Aheadworks\Sarp2\Api\Exception\CouldNotScheduleExceptionInterface
     */
    public function schedule($profiles);

    /**
     * Perform change status action
     *
     * @param int $profileId
     * @param string $status
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changeStatusAction($profileId, $status);

    /**
     * Perform change status action
     *
     * @param int $profileId
     * @param \Magento\Customer\Api\Data\AddressInterface $customerAddress
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changeShippingAddress($profileId, $customerAddress);

    /**
     * Perform change subscription plan
     *
     * @param int $profileId
     * @param int $newPlanId
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changeSubscriptionPlan($profileId, $newPlanId);

    /**
     * Perform change subscription plan
     *
     * @param int $profileId
     * @param string $newNextPaymentDate
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changeNextPaymentDate($profileId, $newNextPaymentDate);

    /**
     * Perform change payment information
     *
     * @param int $profileId
     * @param \Magento\Quote\Api\Data\PaymentInterface $payment
     * @param \Magento\Quote\Api\Data\AddressInterface $billingAddress
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changePaymentInformation(
        $profileId,
        \Magento\Quote\Api\Data\PaymentInterface $payment,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    );

    /**
     * Retrieve profile next payment info
     *
     * @param int $profileId
     * @return \Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNextPaymentInfo($profileId);

    /**
     * Get allowed profile statuses
     *
     * @param int $profileId
     * @return string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllowedStatuses($profileId);

    /**
     * Check if the customer is subscribed to the product
     *
     * @param int $customerId
     * @param int $productId
     * @param int|null $storeId
     * @return bool
     */
    public function isCustomerSubscribedOnProduct($customerId, $productId, $storeId = null);
}
