<?php

namespace Abbott\GlucernaOrders\Model;

use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Abbott\GlucernaOrders\Api\Data\ManagesubscriptionSearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Abbott\GlucernaOrders\Api\ManagesubscriptionRepositoryInterface;
use Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterfaceFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use Abbott\GlucernaOrders\Model\ResourceModel\Managesubscription as ResourceManagesubscription;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Abbott\GlucernaOrders\Model\ResourceModel\Managesubscription\CollectionFactory as
    ManagesubscriptionCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class ManagesubscriptionRepository implements ManagesubscriptionRepositoryInterface
{
    protected $dataObjectHelper;

    protected $dataManagesubscriptionFactory;

    private $storeManager;

    protected $searchResultsFactory;

    protected $dataObjectProcessor;

    protected $extensionAttributesJoinProcessor;

    private $collectionProcessor;

    protected $extensibleDataObjectConverter;

    protected $resource;

    protected $managesubscriptionFactory;

    protected $managesubscriptionCollectionFactory;

    /**
     * @param ResourceManagesubscription $resource
     * @param ManagesubscriptionFactory $managesubscriptionFactory
     * @param ManagesubscriptionInterfaceFactory $dataManagesubscriptionFactory
     * @param ManagesubscriptionCollectionFactory $managesubscriptionCollectionFactory
     * @param ManagesubscriptionSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceManagesubscription $resource,
        ManagesubscriptionFactory $managesubscriptionFactory,
        ManagesubscriptionInterfaceFactory $dataManagesubscriptionFactory,
        ManagesubscriptionCollectionFactory $managesubscriptionCollectionFactory,
        ManagesubscriptionSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->managesubscriptionFactory = $managesubscriptionFactory;
        $this->managesubscriptionCollectionFactory = $managesubscriptionCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataManagesubscriptionFactory = $dataManagesubscriptionFactory;
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
        \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface $managesubscription
    ) {

        $managesubscriptionData = $this->extensibleDataObjectConverter->toNestedArray(
            $managesubscription,
            [],
            \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface::class
        );

        $managesubscriptionModel = $this->managesubscriptionFactory->create()->setData($managesubscriptionData);

        try {
            $this->resource->save($managesubscriptionModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the managesubscription: %1',
                $exception->getMessage()
            ));
        }
        return $managesubscriptionModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($managesubscriptionId)
    {
        $managesubscription = $this->managesubscriptionFactory->create();
        $this->resource->load($managesubscription, $managesubscriptionId);
        if (!$managesubscription->getId()) {
            throw new NoSuchEntityException(__(
                'managesubscription with id "%1" does not exist.',
                $managesubscriptionId
            ));
        }
        return $managesubscription->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->managesubscriptionCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface::class
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
        \Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface $managesubscription
    ) {
        try {
            $managesubscriptionModel = $this->managesubscriptionFactory->create();
            $this->resource->load($managesubscriptionModel, $managesubscription->getManagesubscriptionId());
            $this->resource->delete($managesubscriptionModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the managesubscription: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($managesubscriptionId)
    {
        return $this->delete($this->get($managesubscriptionId));
    }
}
