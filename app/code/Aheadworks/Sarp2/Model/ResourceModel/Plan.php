<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\ResourceModel;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\PlanTitleInterface;
use Aheadworks\Sarp2\Api\Data\PlanTitleInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * todo: handle correct delete operation, check if plan definition is used, delete otherwise
 *       As a part of refactoring/optimization, M2SARP-383
 * Class Plan
 * @package Aheadworks\Sarp2\Model\ResourceModel
 */
class Plan extends AbstractDb
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @var PlanDefinitionInterfaceFactory
     */
    private $definitionFactory;

    /**
     * @var PlanTitleInterfaceFactory
     */
    private $titleFactory;

    /**
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param Factory $dataObjectFactory
     * @param PlanDefinitionInterfaceFactory $definitionFactory
     * @param PlanTitleInterfaceFactory $titleFactory
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        Factory $dataObjectFactory,
        PlanDefinitionInterfaceFactory $definitionFactory,
        PlanTitleInterfaceFactory $titleFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->definitionFactory = $definitionFactory;
        $this->titleFactory = $titleFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_sarp2_plan', 'plan_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->_resources->getConnectionByName(
            $this->metadataPool->getMetadata(PlanInterface::class)->getEntityConnectionName()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $this->saveDefinition($object);
        return parent::_beforeSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->saveTitles($object);
        return parent::_afterSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad(AbstractModel $object)
    {
        $this->loadDefinition($object);
        $this->loadTitles($object);
        return parent::_afterLoad($object);
    }

    /**
     * Save plan frontend titles
     *
     * @param PlanInterface|AbstractModel $plan
     * @return void
     */
    private function saveTitles(PlanInterface $plan)
    {
        $planId = $plan->getPlanId();

        $table = $this->getTable('aw_sarp2_plan_title');
        $connection = $this->getConnection();

        $connection->delete($table, ['plan_id = ?' => $planId]);
        $toInsert = [];
        foreach ($plan->getTitles() as $title) {
            $title->setPlanId($planId);
            $titleData = $this->dataObjectProcessor->buildOutputDataArray(
                $title,
                PlanTitleInterface::class
            );
            $toInsert[] = $this->_prepareDataForTable(
                $this->dataObjectFactory->create($titleData),
                $table
            );
        }
        if ($toInsert) {
            $connection->insertMultiple($table, $toInsert);
        }
    }

    /**
     * Load definition
     *
     * @param PlanInterface|AbstractModel $plan
     * @return void
     */
    private function loadDefinition(PlanInterface $plan)
    {
        $connection = $this->getConnection();

        $definitionId = $plan->getDefinitionId();
        if ($definitionId) {
            $select = $connection->select()
                ->from($this->getTable('aw_sarp2_plan_definition'))
                ->where('definition_id = ?', $definitionId);
            $data = $connection->fetchRow($select);

            /** @var PlanDefinitionInterface $definition */
            $definition = $this->definitionFactory->create();
            $this->dataObjectHelper->populateWithArray($definition, $data, PlanDefinitionInterface::class);
            $plan->setDefinition($definition);
        }
    }

    /**
     * Save plan definition
     *
     * @param PlanInterface|AbstractModel $plan
     * @return void
     */
    private function saveDefinition(PlanInterface $plan)
    {
        $table = $this->getTable('aw_sarp2_plan_definition');
        $connection = $this->getConnection();

        $definition = $plan->getDefinition();
        $definitionId = $definition->getDefinitionId();
        $definitionData = $this->dataObjectProcessor->buildOutputDataArray(
            $definition,
            PlanDefinitionInterface::class
        );
        $data = $this->_prepareDataForTable(
            $this->dataObjectFactory->create($definitionData),
            $table
        );
        if ($definitionId) {
            $connection->update($table, $data, ['definition_id = ?' => $definitionId]);
        } else {
            $connection->insert($table, $data);
            $plan->setDefinitionId($connection->lastInsertId($table));
        }
    }

    /**
     * Load plan titles
     *
     * @param PlanInterface|AbstractModel $plan
     * @return void
     */
    private function loadTitles(PlanInterface $plan)
    {
        $connection = $this->getConnection();

        $planId = $plan->getPlanId();
        if ($planId) {
            $select = $connection->select()
                ->from($this->getTable('aw_sarp2_plan_title'))
                ->where('plan_id = ?', $planId);
            $rows = $connection->fetchAll($select);

            $titles = [];
            foreach ($rows as $row) {
                /** @var PlanTitleInterface $title */
                $title = $this->titleFactory->create();
                $this->dataObjectHelper->populateWithArray($title, $row, PlanTitleInterface::class);
                $titles[] = $title;
            }
            $plan->setTitles($titles);
        }
    }
}
