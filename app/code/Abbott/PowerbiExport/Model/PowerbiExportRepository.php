<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Abbott\PowerbiExport\Model;

use Abbott\PowerbiExport\Model\PowerbiFactory;
use Abbott\PowerbiExport\Api\Data\PowerbiInterfaceFactory;
use Abbott\PowerbiExport\Api\Data\PowerbiSearchResultsInterfaceFactory;
use Abbott\PowerbiExport\Api\PowerbiExportRepositoryInterface;
use Abbott\PowerbiExport\Model\ResourceModel\Powerbi as ResourcePowerbiExport;
use Abbott\PowerbiExport\Model\ResourceModel\Powerbi\CollectionFactory as PowerbiCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class PowerbiExportRepository implements PowerbiExportRepositoryInterface
{

    public $powerbiCollectionFactory;
    /**
     * @var PowerbiExportCollectionFactory
     */
    protected $powerbiExportCollectionFactory;

    /**
     * @var ResourcePowerbiExport
     */
    protected $resource;
    /**
     * @var PowerbiFactory
     */
    protected $powerbiModelFactory;

    /**
     * @var PowerbiInterfaceFactory
     */
    protected $powerbiFactory;

    /**
     * @var PowerbiExport
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @param ResourcePowerbiExport $resource
     * @param PowerbiInterfaceFactory $powerbiFactory
     * @param PowerbiFactory $powerbiModelFactory
     * @param PowerbiCollectionFactory $powerbiCollectionFactory
     * @param PowerbiSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourcePowerbiExport $resource,
        PowerbiInterfaceFactory $powerbiFactory,
        PowerbiFactory $powerbiModelFactory,
        PowerbiCollectionFactory $powerbiCollectionFactory,
        PowerbiSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->powerbiModelFactory = $powerbiModelFactory;
        $this->powerbiFactory = $powerbiFactory;
        $this->powerbiCollectionFactory = $powerbiCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(\Abbott\PowerbiExport\Api\Data\PowerbiInterface $powerbiExport)
    {
        try {
            $this->resource->save($powerbiExport);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the powerbiExport: %1',
                $exception->getMessage()
            ));
        }
        return $powerbiExport;
    }

    /**
     * @inheritDoc
     */
    public function get(int $powerbiExportId)
    {
        $powerbiExport = $this->powerbiFactory->create();
        $this->resource->load($powerbiExport, $powerbiExportId);
        if (!$powerbiExport->getEntityId()) {
            throw new NoSuchEntityException(__('Powerbi Record with id "%1" does not exist.', $powerbiExportId));
        }
        return $powerbiExport;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->powerbiCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(\Abbott\PowerbiExport\Api\Data\PowerbiInterface $powerbiExport)
    {
        try {
            $powerbiExportModel = $this->powerbiFactory->create();
            $this->resource->load($powerbiExportModel, $powerbiExport->getEntityId());
            $this->resource->delete($powerbiExportModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the PowerbiExport: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $powerbiExportId)
    {
        $powerbiExport = $this->powerbiModelFactory->create();
        $this->resource->load($powerbiExport, $powerbiExportId);
        $this->resource->delete($powerbiExport);
    }
}
