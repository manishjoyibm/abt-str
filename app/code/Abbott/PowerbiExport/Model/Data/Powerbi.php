<?php

namespace Abbott\PowerbiExport\Model\Data;

use Abbott\PowerbiExport\Api\Data\PowerbiInterface;

class Powerbi extends \Magento\Framework\Model\AbstractExtensibleModel implements PowerbiInterface
{
    /**
     * Get entityId
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }
    /**
     * Set EntityId
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }
    /**
     * Get report_id
     *
     * @return int
     */
    public function getReportId()
    {
        return $this->_get(self::REPORT_ID);
    }

    /**
     * Set ReportId
     *
     * @param int $reportId
     * @return $this
     */
    public function setReportId($reportId)
    {
        return $this->setData(self::REPORT_ID, $reportId);
    }

    /**
     * Get ReportName
     *
     * @return string
     */
    public function getReportName()
    {
        return $this->_get(self::REPORT_NAME);
    }

    /**
     * Set ReportName
     *
     * @param string $reportName
     * @return $this
     */
    public function setReportName($reportName)
    {
        return $this->setData(self::REPORT_NAME, $reportName);
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * Set Status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get LastCronUpdateDate
     *
     * @return string
     */
    public function getLastCronUpdateDate()
    {
        return $this->_get(self::LAST_CRON_UPDATE_DATE);
    }

    /**
     *  Set last_cron_update_date
     *
     * @param string $lastCronUpdateDate
     * @return $this
     */
    public function setLastCronUpdateDate($lastCronUpdateDate)
    {
        return $this->setData(self::LAST_CRON_UPDATE_DATE, $lastCronUpdateDate);
    }

    /**
     * Get CreatedAt
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get UpdatedAt
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Set updatedAt
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Abbott\PowerbiExport\Api\Data\PowerbiExportExtensionInterface
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Abbott\PowerbiExport\Api\Data\PowerbiExportExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Abbott\PowerbiExport\Api\Data\PowerbiExportExtensionInterface $extensionAttributes
    )
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
