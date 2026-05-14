<?php

namespace Abbott\ProgressiveDiscount\Model;

use Abbott\ProgressiveDiscount\Model\ManageMonthlySubscriptionsFactory;
use Abbott\ProgressiveDiscount\Api\ManageMonthlySubscriptionsRepositoryInterface;
use Abbott\ProgressiveDiscount\Api\Data\ManageMonthlySubscriptionsSearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Abbott\ProgressiveDiscount\Model\ResourceModel\ManageMonthlySubscriptions as ResourceManageMonthlySubscriptions;
use Abbott\ProgressiveDiscount\Model\ResourceModel\ManageMonthlySubscriptions\CollectionFactory as
    ManageMonthlySubscriptionsCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class ManageMonthlySubscriptionsRepository implements ManageMonthlySubscriptionsRepositoryInterface
{
    /**
     * @var ManageMonthlySubscriptionsFeed
     */
    protected $resource;

    /**
     * @var ManageMonthlySubscriptionsFactory
     */
    protected $manageMonthlySubscriptionsFactory;

    /**
     * @var ManageMonthlySubscriptionsCollectionFactory
     */
    protected $manageMonthlySubscriptionsCollectionFactory;

    /**
     * @var Data\ManageMonthlySubscriptionsSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param ResourcePage $resource
     * @param ManageMonthlySubscriptionsFactory $ManageMonthlySubscriptionsFactory
     * @param Data\ManageMonthlySubscriptionsInterface $dataManageMonthlySubscriptions
     * @param ManageMonthlySubscriptionsCollectionFactory $manageMonthlySubscriptionsCollectionFactory
     * @param Data\ManageMonthlySubscriptionsSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceManageMonthlySubscriptions $resource,
        ManageMonthlySubscriptionsFactory $manageMonthlySubscriptionsFactory,
        ManageMonthlySubscriptionsCollectionFactory $manageMonthlySubscriptionsCollectionFactory,
        ManageMonthlySubscriptionsSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->resource = $resource;
        $this->manageMonthlySubscriptionsFactory = $manageMonthlySubscriptionsFactory;
        $this->manageMonthlySubscriptionsCollectionFactory = $manageMonthlySubscriptionsCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Save ManageMonthlySubscriptions data
     *
     * @param \Abbott\ProgressiveDiscount\Api\Data\ManageMonthlySubscriptionsInterface $manageMonthlySubscriptions
     * @return ManageMonthlySubscriptions
     * @throws CouldNotSaveException
     */
    public function save(\Abbott\ProgressiveDiscount\Api\Data\ManageMonthlySubscriptionsInterface
                         $manageMonthlySubscriptions)
    {
        if ($manageMonthlySubscriptions->getStoreId() === null) {
            $storeId = $this->storeManager->getStore()->getId();
            $manageMonthlySubscriptions->setStoreId($storeId);
        }
        try {
            $this->resource->save($manageMonthlySubscriptions);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the MonthlySubscription: %1', $exception->getMessage()),
                $exception
            );
        }
        return $manageMonthlySubscriptions;
    }

    /**
     * Load ManageMonthlySubscriptions data by given Page Identity
     *
     * @param string $rowId
     * @return ManageMonthlySubscriptions
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($rowId)
    {
        $manageMonthlySubscriptions = $this->manageMonthlySubscriptionsFactory->create();
        $manageMonthlySubscriptions->load($rowId);
        if (!$manageMonthlySubscriptions->getRowId()) {
            throw new NoSuchEntityException(__('The MonthlySubscription with the "%1" ID doesn\'t exist.', $rowId));
        }
        return $rowId;
    }

    /**
     * Load ManageMonthlySubscriptions collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $collection = $this->manageMonthlySubscriptionsCollectionFactory->create();

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
     * @param \Abbott\ProgressiveDiscount\Api\Data\ManageMonthlySubscriptionsInterface $manageMonthlySubscriptions
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Abbott\ProgressiveDiscount\Api\Data\ManageMonthlySubscriptionsInterface $rowId)
    {
        try {
            $this->resource->delete($rowId);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Feed: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * Delete ManageMonthlySubscriptions by given RowID Identity
     *
     * @param string $rowId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($rowId)
    {
        return $this->delete($this->getById($rowId));
    }
}
