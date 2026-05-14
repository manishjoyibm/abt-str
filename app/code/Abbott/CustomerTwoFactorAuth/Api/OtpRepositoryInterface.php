<?php
namespace Abbott\CustomerTwoFactorAuth\Api;

interface OtpRepositoryInterface
{
    /**
     * Get by id
     *
     * @param int $id
     * @return \Abbott\CustomerTwoFactorAuth\Api\Data\OtpInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Save function
     *
     * @param \Abbott\CustomerTwoFactorAuth\Api\Data\OtpInterface $student
     * @return \Abbott\CustomerTwoFactorAuth\Api\Data\OtpInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Abbott\CustomerTwoFactorAuth\Api\Data\OtpInterface $student);

    /**
     * Delete function
     *
     * @param \Abbott\CustomerTwoFactorAuth\Api\Data\OtpInterface $student
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Abbott\CustomerTwoFactorAuth\Api\Data\OtpInterface $student);

    /**
     * Get list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Abbott\CustomerTwoFactorAuth\Api\Data\OtpSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
