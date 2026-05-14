<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Abbott\MetabolicOrdering\Model;

use Abbott\MetabolicOrdering\Api\Data\MetabolicInterface;
use Abbott\MetabolicOrdering\Model\MetabolicFactory;
use Abbott\MetabolicOrdering\Api\Data\MetabolicInterfaceFactory;
use Abbott\MetabolicOrdering\Api\Data\MetabolicSearchResultsInterfaceFactory;
use Abbott\MetabolicOrdering\Api\MetabolicOrderingRepositoryInterface;
use Abbott\MetabolicOrdering\Model\ResourceModel\Metabolic as ResourceMetabolicOrdering;
use Abbott\MetabolicOrdering\Model\ResourceModel\Metabolic\CollectionFactory as MetabolicCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class MetabolicOrderingRepository implements MetabolicOrderingRepositoryInterface
{

    public $metabolicCollectionFactory;
    /**
     * @var MetabolicOrderingCollectionFactory
     */
    protected $metabolicOrderingCollectionFactory;

    /**
     * @var ResourceMetabolicOrdering
     */
    protected $resource;
    /**
     * @var MetabolicFactory
     */
    protected $metabolicModelFactory;

    /**
     * @var MetabolicInterfaceFactory
     */
    protected $metabolicFactory;

    /**
     * @var MetabolicOrdering
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @param ResourceMetabolicOrdering $resource
     * @param MetabolicInterfaceFactory $metabolicFactory
     * @param MetabolicFactory $metabolicModelFactory
     * @param MetabolicCollectionFactory $metabolicCollectionFactory
     * @param MetabolicSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceMetabolicOrdering $resource,
        MetabolicInterfaceFactory $metabolicFactory,
        MetabolicFactory $metabolicModelFactory,
        MetabolicCollectionFactory $metabolicCollectionFactory,
        MetabolicSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->metabolicModelFactory = $metabolicModelFactory;
        $this->metabolicFactory = $metabolicFactory;
        $this->metabolicCollectionFactory = $metabolicCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(\Abbott\MetabolicOrdering\Api\Data\MetabolicInterface $metabolicOrdering)
    {
        try {
            $this->resource->save($metabolicOrdering);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the metabolicOrdering: %1',
                $exception->getMessage()
            ));
        }
        return $metabolicOrdering;
    }

    /**
     * @inheritDoc
     */
    public function get($metabolicOrderingId)
    {
        $metabolicOrdering = $this->metabolicFactory->create();
        $this->resource->load($metabolicOrdering, $metabolicOrderingId);
        if (!$metabolicOrdering->getEntityId()) {
            throw new NoSuchEntityException(__('Metabolic Record with id "%1" does not exist.', $metabolicOrderingId));
        }
        return $metabolicOrdering;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->metabolicCollectionFactory->create();
        
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
    public function delete(\Abbott\MetabolicOrdering\Api\Data\MetabolicInterface $metabolicOrdering)
    {
        try {
            $metabolicOrderingModel = $this->metabolicFactory->create();
            $this->resource->load($metabolicOrderingModel, $metabolicOrdering->getEntityId());
            $this->resource->delete($metabolicOrderingModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the MetabolicOrdering: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($metabolicOrderingId)
    {
        $metabolicOrdering = $this->metabolicModelFactory->create();
        $this->resource->load($metabolicOrdering, $metabolicOrderingId);
        $this->resource->delete($metabolicOrdering);
    }
}
