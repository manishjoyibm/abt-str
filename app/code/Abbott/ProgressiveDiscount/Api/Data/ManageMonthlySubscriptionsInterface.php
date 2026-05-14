<?php

namespace Abbott\ProgressiveDiscount\Api\Data;

interface ManageMonthlySubscriptionsInterface
{
    public const ROW_ID              = 'row_id';
    public const PROFILE_ID          = 'profile_id';
    public const CREATED_AT          = 'created_at';
    public const UPDATED_AT          = 'updated_at';
    public const CURRENT_MONTH       = 'current_month';
    public const CUSTOMER_EMAIL      = 'customer_email';
    public const STATUS              = 'status';

    /**
     * Get RowId
     *
     * @return int|null
     */
    public function getRowId();
    /**
     * Get ProfileId
     *
     * @return string|null
     */
    public function getProfileId();

    /**
     * Get CreatedAt
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Get UpdatedAt
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Get status
     *
     * @return string|null
     */
    public function getStatus();

    /**
     * Get CurrentMonth
     *
     * @return string|null
     */
    public function getCurrentMonth();

    /**
     * Get CustomerEmail
     *
     * @return string|null
     */
    public function getCustomerEmail();

    /**
     * Set RowId
     *
     * @param int $rowId
     * @return ManageMonthlySubscriptionsInterface
     */
    public function setRowId($rowId);

    /**
     * Set ProfileId
     *
     * @param string $profileId
     * @return ManageMonthlySubscriptionsInterface
     */
    public function setProfileId($profileId);

    /**
     * Set CreatedAt
     *
     * @param string $createdAt
     * @return ManageMonthlySubscriptionsInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set UpdatedAt
     *
     * @param string $updatedAt
     * @return ManageMonthlySubscriptionsInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Set Status
     *
     * @param string $status
     * @return ManageMonthlySubscriptionsInterface
     */
    public function setStatus($status);

    /**
     * Set CurrentMonth
     *
     * @param string $currentMonth
     * @return ManageMonthlySubscriptionsInterface
     */
    public function setCurrentMonth($currentMonth);

    /**
     * Set CustomerEmail
     *
     * @param string $customerEmail
     * @return ManageMonthlySubscriptionsInterface
     */
    public function setCustomerEmail($customerEmail);
}
