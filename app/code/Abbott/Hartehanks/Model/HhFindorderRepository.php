<?php

namespace Abbott\Hartehanks\Model;

use Abbott\Hartehanks\Api\Data\HhFindorderInterfaceFactory;
use Abbott\Hartehanks\Api\Data\HhFindorderSearchResultsInterfaceFactory;
use Abbott\Hartehanks\Api\HhFindorderRepositoryInterface;
use Abbott\Hartehanks\Model\ResourceModel\HhFindorder as ResourceHhFindorder;
use Abbott\Hartehanks\Model\ResourceModel\HhFindorder\CollectionFactory as HhFindorderCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class HhFindorderRepository implements HhFindorderRepositoryInterface
{
    protected $hhFindorderFactory;

    protected $extensionAttributesJoinProcessor;

    protected $searchResultsFactory;

    private $storeManager;

    protected $dataObjectProcessor;

    protected $dataObjectHelper;

    protected $dataHhFindorderFactory;

    protected $extensibleDataObjectConverter;

    protected $resource;

    protected $hhFindorderCollectionFactory;

    private $collectionProcessor;

    /**
     * @param ResourceHhFindorder $resource
     * @param HhFindorderFactory $hhFindorderFactory
     * @param HhFindorderInterfaceFactory $dataHhFindorderFactory
     * @param HhFindorderCollectionFactory $hhFindorderCollectionFactory
     * @param HhFindorderSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceHhFindorder $resource,
        HhFindorderFactory $hhFindorderFactory,
        HhFindorderInterfaceFactory $dataHhFindorderFactory,
        HhFindorderCollectionFactory $hhFindorderCollectionFactory,
        HhFindorderSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->hhFindorderFactory = $hhFindorderFactory;
        $this->hhFindorderCollectionFactory = $hhFindorderCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataHhFindorderFactory = $dataHhFindorderFactory;
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
        \Abbott\Hartehanks\Api\Data\HhFindorderInterface $hhFindorder
    ) {
        $hhFindorderData = $this->extensibleDataObjectConverter->toNestedArray(
            $hhFindorder,
            [],
            \Abbott\Hartehanks\Api\Data\HhFindorderInterface::class
        );

        $hhFindorderModel = $this->hhFindorderFactory->create()->setData($hhFindorderData);

        try {
            $this->resource->save($hhFindorderModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the hhFindorder: %1',
                $exception->getMessage()
            ));
        }
        return $hhFindorderModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($hhFindorderId)
    {
        $hhFindorder = $this->hhFindorderFactory->create();
        $this->resource->load($hhFindorder, $hhFindorderId);
        if (!$hhFindorder->getId()) {
            throw new NoSuchEntityException(__('HhFindorder with id "%1" does not exist.', $hhFindorderId));
        }
        return $hhFindorder->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->hhFindorderCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Abbott\Hartehanks\Api\Data\HhFindorderInterface::class
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
        \Abbott\Hartehanks\Api\Data\HhFindorderInterface $hhFindorder
    ) {
        try {
            $hhFindorderModel = $this->hhFindorderFactory->create();
            $this->resource->load($hhFindorderModel, $hhFindorder->getHhfindorderId());
            $this->resource->delete($hhFindorderModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the HhFindorder: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($hhFindorderId)
    {
        return $this->delete($this->get($hhFindorderId));
    }
}
