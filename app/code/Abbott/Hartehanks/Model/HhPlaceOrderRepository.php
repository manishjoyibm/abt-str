<?php

namespace Abbott\Hartehanks\Model;

use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Abbott\Hartehanks\Model\ResourceModel\HhPlaceOrder as ResourceHhPlaceOrder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Abbott\Hartehanks\Model\ResourceModel\HhPlaceOrder\CollectionFactory as HhPlaceOrderCollectionFactory;
use Abbott\Hartehanks\Api\HhPlaceOrderRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Abbott\Hartehanks\Api\Data\HhPlaceOrderSearchResultsInterfaceFactory;
use Abbott\Hartehanks\Api\Data\HhPlaceOrderInterfaceFactory;

class HhPlaceOrderRepository implements HhPlaceOrderRepositoryInterface
{
    protected $dataObjectHelper;

    private $storeManager;

    protected $searchResultsFactory;

    protected $dataObjectProcessor;

    protected $extensionAttributesJoinProcessor;

    private $collectionProcessor;

    protected $extensibleDataObjectConverter;
    protected $resource;

    protected $hhPlaceOrderFactory;

    protected $hhPlaceOrderCollectionFactory;

    protected $dataHhPlaceOrderFactory;

    /**
     * @param ResourceHhPlaceOrder $resource
     * @param HhPlaceOrderFactory $hhPlaceOrderFactory
     * @param HhPlaceOrderInterfaceFactory $dataHhPlaceOrderFactory
     * @param HhPlaceOrderCollectionFactory $hhPlaceOrderCollectionFactory
     * @param HhPlaceOrderSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceHhPlaceOrder $resource,
        HhPlaceOrderFactory $hhPlaceOrderFactory,
        HhPlaceOrderInterfaceFactory $dataHhPlaceOrderFactory,
        HhPlaceOrderCollectionFactory $hhPlaceOrderCollectionFactory,
        HhPlaceOrderSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->hhPlaceOrderFactory = $hhPlaceOrderFactory;
        $this->hhPlaceOrderCollectionFactory = $hhPlaceOrderCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataHhPlaceOrderFactory = $dataHhPlaceOrderFactory;
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
        \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface $hhPlaceOrder
    ) {
        $hhPlaceOrderData = $this->extensibleDataObjectConverter->toNestedArray(
            $hhPlaceOrder,
            [],
            \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface::class
        );

        $hhPlaceOrderModel = $this->hhPlaceOrderFactory->create()->setData($hhPlaceOrderData);

        try {
            $this->resource->save($hhPlaceOrderModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the hhPlaceOrder: %1',
                $exception->getMessage()
            ));
        }
        return $hhPlaceOrderModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($hhPlaceOrderId)
    {
        $hhPlaceOrder = $this->hhPlaceOrderFactory->create();
        $this->resource->load($hhPlaceOrder, $hhPlaceOrderId);
        if (!$hhPlaceOrder->getId()) {
            throw new NoSuchEntityException(__('HhPlaceOrder with id "%1" does not exist.', $hhPlaceOrderId));
        }
        return $hhPlaceOrder->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->hhPlaceOrderCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface::class
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
        \Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface $hhPlaceOrder
    ) {
        try {
            $hhPlaceOrderModel = $this->hhPlaceOrderFactory->create();
            $this->resource->load($hhPlaceOrderModel, $hhPlaceOrder->getHhplaceorderId());
            $this->resource->delete($hhPlaceOrderModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the HhPlaceOrder: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($hhPlaceOrderId)
    {
        return $this->delete($this->get($hhPlaceOrderId));
    }
}
