<?php

namespace Abbott\Hartehanks\Model;

use Abbott\Hartehanks\Api\HarteHankRepositoryInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Abbott\Hartehanks\Api\Data\HarteHankInterfaceFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use Abbott\Hartehanks\Model\ResourceModel\HarteHank as ResourceHarteHank;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Abbott\Hartehanks\Api\Data\HarteHankSearchResultsInterfaceFactory;
use Abbott\Hartehanks\Model\ResourceModel\HarteHank\CollectionFactory as HarteHankCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class HarteHankRepository implements HarteHankRepositoryInterface
{
    protected $dataObjectHelper;

    protected $harteHankCollectionFactory;

    private $storeManager;

    protected $searchResultsFactory;

    protected $dataObjectProcessor;

    protected $extensionAttributesJoinProcessor;

    protected $dataHarteHankFactory;

    private $collectionProcessor;

    protected $resource;

    protected $extensibleDataObjectConverter;

    protected $harteHankFactory;

    /**
     * @param ResourceHarteHank $resource
     * @param HarteHankFactory $harteHankFactory
     * @param HarteHankInterfaceFactory $dataHarteHankFactory
     * @param HarteHankCollectionFactory $harteHankCollectionFactory
     * @param HarteHankSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceHarteHank $resource,
        HarteHankFactory $harteHankFactory,
        HarteHankInterfaceFactory $dataHarteHankFactory,
        HarteHankCollectionFactory $harteHankCollectionFactory,
        HarteHankSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->harteHankFactory = $harteHankFactory;
        $this->harteHankCollectionFactory = $harteHankCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataHarteHankFactory = $dataHarteHankFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Abbott\Hartehanks\Api\Data\HarteHankInterface $harteHank
    ) {

        $harteHankData = $this->extensibleDataObjectConverter->toNestedArray(
            $harteHank,
            [],
            \Abbott\Hartehanks\Api\Data\HarteHankInterface::class
        );

        $harteHankModel = $this->harteHankFactory->create()->setData($harteHankData);

        try {
            $this->resource->save($harteHankModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the harteHank: %1',
                $exception->getMessage()
            ));
        }
        return $harteHankModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($harteHankId)
    {
        $harteHank = $this->harteHankFactory->create();
        $this->resource->load($harteHank, $harteHankId);
        if (!$harteHank->getId()) {
            throw new NoSuchEntityException(__('HarteHank with id "%1" does not exist.', $harteHankId));
        }
        return $harteHank->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->harteHankCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Abbott\Hartehanks\Api\Data\HarteHankInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Abbott\Hartehanks\Api\Data\HarteHankInterface $harteHank
    ) {
        try {
            $harteHankModel = $this->harteHankFactory->create();
            $this->resource->load($harteHankModel, $harteHank->getHartehankId());
            $this->resource->delete($harteHankModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the HarteHank: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($harteHankId)
    {
        return $this->delete($this->get($harteHankId));
    }
}
