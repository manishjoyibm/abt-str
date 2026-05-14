<?php

namespace Abbott\PowerbiExport\Api\Data;

interface PowerbiInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    public const ENTITY_ID          ='entity_id';
    public const REPORT_ID          ='report_id';
    public const CREATED_AT         ='created_at';
    public const UPDATED_AT         ='updated_at';
    public const REPORT_NAME        ='report_name';
    public const LAST_CRON_UPDATE_DATE ='last_cron_update_date';
    
    /**
     * Get EntityId
     *
     * @return int|null
     */
    public function getEntityId();
    /**
     * Get ReportName
     *
     * @return string|null
     */
    public function getReportName();

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
     * Get LastCronUpdateDate
     *
     * @return string|null
     */
    public function getLastCronUpdateDate();

    /**
     * Get ReportId
     *
     * @return int|null
     */
    public function getReportId();

    /**
     * Set EntityId
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);
    /**
     * Set ReportId
     *
     * @param int $customerId
     * @return $this
     */
    public function setReportId($customerId);
    /**
     * Set CreatedAt
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);
    
    /**
     * Set UpdatedAt
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
   
    /**
     * Set ReportName
     *
     * @param string $customerEmail
     * @return $this
     */
    public function setReportName($customerEmail);
    /**
     * Set LastCronUpdateDate
     *
     * @param string $expiryDate
     * @return $this
     */
    public function setLastCronUpdateDate($expiryDate);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Abbott\PowerbiExport\Api\Data\PowerbiExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Abbott\PowerbiExport\Api\Data\PowerbiExportExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Abbott\PowerbiExport\Api\Data\PowerbiExportExtensionInterface $extensionAttributes
    );
}
