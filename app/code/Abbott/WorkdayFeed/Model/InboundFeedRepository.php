<?php

namespace Abbott\WorkdayFeed\Model;

use Abbott\WorkdayFeed\Api\Data;
use Abbott\WorkdayFeed\Api\Data\InboundFeedSearchResultsInterfaceFactory;
use Abbott\WorkdayFeed\Api\InboundFeedRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Abbott\WorkdayFeed\Model\ResourceModel\InboundFeed as ResourceInboundFeed;
use Abbott\WorkdayFeed\Model\ResourceModel\InboundFeed\CollectionFactory as InboundFeedCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class InboundFeedRepository implements InboundFeedRepositoryInterface
{
    /**
     * @var ResourceInboundFeed
     */
    protected ResourceInboundFeed $resource;

    /**
     * @var InboundFeedFactory
     */
    protected InboundFeedFactory $inboundFeedFactory;

    /**
     * @var inboundFeedCollectionFactory
     */
    protected InboundFeedCollectionFactory $inboundFeedCollectionFactory;

    /**
     * @var Data\PageSearchResultsInterfaceFactory
     */
    protected InboundFeedSearchResultsInterfaceFactory|Data\PageSearchResultsInterfaceFactory $searchResultsFactory;

    /**
     * @var DataObjectProcessor
     */
    protected DataObjectProcessor $dataObjectProcessor;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private CollectionProcessorInterface $collectionProcessor;

    /**
     * @param ResourceInboundFeed $resource
     * @param InboundFeedFactory $inboundFeedFactory
     * @param InboundFeedCollectionFactory $inboundFeedCollectionFactory
     * @param InboundFeedSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ResourceInboundFeed $resource,
        InboundFeedFactory $inboundFeedFactory,
        InboundFeedCollectionFactory $inboundFeedCollectionFactory,
        Data\InboundFeedSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->resource = $resource;
        $this->inboundFeedFactory = $inboundFeedFactory;
        $this->inboundFeedCollectionFactory = $inboundFeedCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Save Inbound feed data
     *
     * @param \Abbott\WorkdayFeed\Api\Data\InboundFeedInterface $inboundFeed
     * @return Inbound Feed
     * @throws CouldNotSaveException
     */
    public function save(\Abbott\WorkdayFeed\Api\Data\InboundFeedInterface $inboundFeed): Data\InboundFeedInterface
    {
        if ($inboundFeed->getStoreId() === null) {
            $storeId = $this->storeManager->getStore()->getId();
            $inboundFeed->setStoreId($storeId);
        }
        try {
            $this->resource->save($inboundFeed);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the Inbound Feed: %1', $exception->getMessage()),
                $exception
            );
        }
        return $inboundFeed;
    }

    /**
     * Load Inbound feed data by given Page Identity
     *
     * @param int $feedId
     * @return InboundFeed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $feedId): Data\InboundFeedInterface
    {
        $inboundFeed = $this->inboundFeedFactory->create();
        $inboundFeed->load($feedId);
        if (!$inboundFeed->getFeedId()) {
            throw new NoSuchEntityException(__('The Inbound Feed with the "%1" ID doesn\'t exist.', $feedId));
        }
        return $feedId;
    }

    /**
     * Load Inbound data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ): Data\InboundFeedSearchResultsInterface
    {
        $collection = $this->inboundFeedCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete InboundFeed
     *
     * @param \Abbott\WorkdayFeed\Api\Data\InboundFeedInterface $inboundFeed
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Abbott\WorkdayFeed\Api\Data\InboundFeedInterface $feedId): bool
    {
        try {
            $this->resource->delete($feedId);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Feed: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * Delete InboundFeed by given InboundFeed Identity
     *
     * @param int $feedId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $feedId)
    {
        return $this->delete($this->getById($feedId));
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 102.0.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Abbott\WorkdayFeed\Model\Api\SearchCriteria\InboundFeedCollectionProcessor::class
            );
        }
        return $this->collectionProcessor;
    }
}
