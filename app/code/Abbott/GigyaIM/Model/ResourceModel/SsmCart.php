<?php

namespace Abbott\GigyaIM\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SsmCart extends AbstractDb
{
    /**
     * Construct function
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ssm_shopping_cart', 'id');
    }

    /**
     * GetByEmail And Website
     *
     * @param string $email
     * @param int $websiteId
     * @return mixed
     * @throws LocalizedException
     */
    public function getByEmailAndWebsite($email, $websiteId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable()
        )->where(
            'email = :email'
        )->where(
            'website_id = :website_id'
        );
        return $this->getConnection()->fetchRow(
            $select,
            [':email' => $email, ':website_id' => $websiteId]
        );
    }
}
