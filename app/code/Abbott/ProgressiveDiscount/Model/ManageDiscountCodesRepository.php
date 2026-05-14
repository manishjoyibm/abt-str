<?php

namespace Abbott\ProgressiveDiscount\Model;

use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Abbott\ProgressiveDiscount\Model\ResourceModel\ManageDiscountCodes as ResourceManageDiscountCodes;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesSearchResultsInterfaceFactory;
use Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesInterfaceFactory;
use Abbott\ProgressiveDiscount\Api\ManageDiscountCodesRepositoryInterface;
use Abbott\ProgressiveDiscount\Model\ResourceModel\ManageDiscountCodes\CollectionFactory as
    ManageDiscountCodesCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class ManageDiscountCodesRepository implements ManageDiscountCodesRepositoryInterface
{

    protected $manageDiscountCodesFactory;

    protected $dataObjectHelper;

    private $storeManager;

    protected $searchResultsFactory;

    protected $dataObjectProcessor;

    protected $extensionAttributesJoinProcessor;

    private $collectionProcessor;

    protected $manageDiscountCodesCollectionFactory;

    protected $resource;

    protected $extensibleDataObjectConverter;

    protected $dataManageDiscountCodesFactory;

    /**
     * @param ResourceManageDiscountCodes $resource
     * @param ManageDiscountCodesFactory $manageDiscountCodesFactory
     * @param ManageDiscountCodesInterfaceFactory $dataManageDiscountCodesFactory
     * @param ManageDiscountCodesCollectionFactory $manageDiscountCodesCollectionFactory
     * @param ManageDiscountCodesSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceManageDiscountCodes $resource,
        ManageDiscountCodesFactory $manageDiscountCodesFactory,
        ManageDiscountCodesInterfaceFactory $dataManageDiscountCodesFactory,
        ManageDiscountCodesCollectionFactory $manageDiscountCodesCollectionFactory,
        ManageDiscountCodesSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->manageDiscountCodesFactory = $manageDiscountCodesFactory;
        $this->manageDiscountCodesCollectionFactory = $manageDiscountCodesCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataManageDiscountCodesFactory = $dataManageDiscountCodesFactory;
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
        \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesInterface $manageDiscountCodes
    ) {
        $manageDiscountCodesData = $this->extensibleDataObjectConverter->toNestedArray(
            $manageDiscountCodes,
            [],
            \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesInterface::class
        );

        $manageDiscountCodesModel = $this->manageDiscountCodesFactory->create()->setData($manageDiscountCodesData);

        try {
            $this->resource->save($manageDiscountCodesModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the DiscountCodes: %1',
                $exception->getMessage()
            ));
        }
        return $manageDiscountCodesModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($manageDiscountCodesId)
    {
        $manageDiscountCodes = $this->manageDiscountCodesFactory->create();
        $this->resource->load($manageDiscountCodes, $manageDiscountCodesId);
        if (!$manageDiscountCodes->getId()) {
            throw new NoSuchEntityException(__('DiscountCode with id "%1" does not exist.', $manageDiscountCodesId));
        }
        return $manageDiscountCodes->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->manageDiscountCodesCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesInterface::class
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
        \Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesInterface $manageDiscountCodes
    ) {
        try {
            $manageDiscountCodesModel = $this->manageDiscountCodesFactory->create();
            $this->resource->load($manageDiscountCodesModel, $manageDiscountCodes->getManagediscountcodesId());
            $this->resource->delete($manageDiscountCodesModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the ManageDiscountCodes: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($manageDiscountCodesId)
    {
        return $this->delete($this->get($manageDiscountCodesId));
    }
}
