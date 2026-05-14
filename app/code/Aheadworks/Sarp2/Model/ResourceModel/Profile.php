<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\ResourceModel;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Profile as ProfileModel;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\HandlerInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\SalesSequence\Model\Manager as SequenceManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Profile
 * @package Aheadworks\Sarp2\Model\ResourceModel
 */
class Profile extends AbstractDb
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SequenceManager
     */
    private $sequenceManager;

    /**
     * @var PlanDefinitionInterfaceFactory
     */
    private $planDefinitionFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * todo: should be pool with sorting, M2SARP-383
     * @var HandlerInterface[]
     */
    private $saveHandlers = [];

    /**
     * @var HandlerInterface[]
     */
    private $deleteHandlers = [];

    /**
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param StoreManagerInterface $storeManager
     * @param SequenceManager $sequenceManager
     * @param PlanDefinitionInterfaceFactory $planDefinitionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param array $saveHandlers
     * @param array $deleteHandlers
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        StoreManagerInterface $storeManager,
        SequenceManager $sequenceManager,
        PlanDefinitionInterfaceFactory $planDefinitionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        $saveHandlers = [],
        $deleteHandlers = [],
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool;
        $this->storeManager = $storeManager;
        $this->sequenceManager = $sequenceManager;
        $this->planDefinitionFactory = $planDefinitionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->saveHandlers = $saveHandlers;
        $this->deleteHandlers = $deleteHandlers;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_sarp2_profile', 'profile_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->_resources->getConnectionByName(
            $this->metadataPool->getMetadata(ProfileInterface::class)->getEntityConnectionName()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->joinLeft(
            ['customer_entity_table' => $this->getTable('customer_entity')],
            $this->getMainTable() . '.customer_id = customer_entity_table.entity_id',
            ['group_id']
        )->columns(
            [
                'customer_group_id' => $this->getConnection()->getIfNullSql(
                    'customer_entity_table.group_id',
                    new \Zend_Db_Expr('0')
                )
            ]
        );

        return $select;
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /** @var ProfileInterface|AbstractModel $object */
        if (!$object->getProfileId()) {
            $object->setStatus(Status::PENDING);
            $this->saveDefinition($object);
        }
        if (!$object->getIncrementId()) {
            $store = $this->storeManager->getStore($object->getStoreId());
            $group = $this->storeManager->getGroup($store->getStoreGroupId());
            $sequence = $this->sequenceManager->getSequence(
                ProfileModel::ENTITY,
                $group->getDefaultStoreId()
            );
            $object->setIncrementId($sequence->getNextValue());
        }
        if ($object->getProfileId()
            && $object->getPlanId() != $object->getOrigData(ProfileInterface::PLAN_ID)
            && $object->getPlanDefinitionId() != $object->getOrigData(ProfileInterface::PLAN_DEFINITION_ID)
        ) {
            $this->removeDefinition($object->getProfileDefinitionId());
            $this->saveDefinition($object);
        }
        return parent::_beforeSave($object);
    }

    /**
     * Remove profile definition id
     *
     * @param int $definitionId
     */
    private function removeDefinition($definitionId)
    {
        $connection = $this->getConnection();
        $table = $this->getTable('aw_sarp2_profile_definition');

        $connection->delete($table, ['definition_id = ?' => $definitionId]);
    }

    /**
     * Save definition
     *
     * @param ProfileInterface $profile
     */
    private function saveDefinition(ProfileInterface $profile)
    {
        $connection = $this->getConnection();
        $table = $this->getTable('aw_sarp2_profile_definition');
        $definition = $profile->getPlanDefinition();
        $definitionData = $this->dataObjectProcessor->buildOutputDataArray(
            $definition,
            PlanDefinitionInterface::class
        );
        unset($definitionData[PlanDefinitionInterface::DEFINITION_ID]);

        $connection->insert($table, $definitionData);
        $profile->setProfileDefinitionId($connection->lastInsertId($table));
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterSave(AbstractModel $object)
    {
        /** @var ProfileInterface|AbstractModel $object */
        foreach ($this->saveHandlers as $handler) {
            $handler->process($object);
        }
        return parent::_afterSave($object);
    }

    /**
     * todo: duplication in \Aheadworks\Sarp2\Model\ResourceModel\Plan::loadDefinition(), M2SARP-383
     * Load plan definition
     *
     * @param int $definitionId
     * @param bool $isPlan
     * @return PlanDefinitionInterface
     */
    public function loadDefinition($definitionId, $isPlan = true)
    {
        $connection = $this->getConnection();
        $table = $isPlan
            ? $this->getTable('aw_sarp2_plan_definition')
            : $this->getTable('aw_sarp2_profile_definition');
        $select = $connection->select()
            ->from($table)
            ->where('definition_id = ?', $definitionId);
        $data = $connection->fetchRow($select);

        /** @var PlanDefinitionInterface $definition */
        $definition = $this->planDefinitionFactory->create();
        if ($data) {
            $this->dataObjectHelper->populateWithArray($definition, $data, PlanDefinitionInterface::class);
        }
        return $definition;
    }
}
