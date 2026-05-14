<?php

namespace Abbott\Webhook\Model;

use Abbott\Webhook\Api\Data\WebhookInterface;
use Abbott\Webhook\Api\Data\WebhookSearchResultsInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Abbott\Webhook\Model\ResourceModel\Webhook as ResourceWebhook;
use Magento\Framework\Exception\CouldNotDeleteException;
use Abbott\Webhook\Api\WebhookRepositoryInterface;
use Abbott\Webhook\Api\Data\WebhookSearchResultsInterfaceFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Abbott\Webhook\Api\Data\WebhookInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Abbott\Webhook\Model\ResourceModel\Webhook\CollectionFactory as WebhookCollectionFactory;

class WebhookRepository implements WebhookRepositoryInterface
{
    protected $dataObjectHelper;
    private $storeManager;
    protected $webhookFactory;
    protected $webhookCollectionFactory;
    protected $dataWebhookFactory;
    protected $searchResultsFactory;

    protected $dataObjectProcessor;

    protected $extensionAttributesJoinProcessor;

    private $collectionProcessor;

    protected $extensibleDataObjectConverter;

    protected $resource;

    /**
     * Constructor
     *
     * @param ResourceWebhook $resource
     * @param WebhookFactory $webhookFactory
     * @param WebhookInterfaceFactory $dataWebhookFactory
     * @param WebhookCollectionFactory $webhookCollectionFactory
     * @param WebhookSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceWebhook $resource,
        WebhookFactory $webhookFactory,
        WebhookInterfaceFactory $dataWebhookFactory,
        WebhookCollectionFactory $webhookCollectionFactory,
        WebhookSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->webhookFactory = $webhookFactory;
        $this->webhookCollectionFactory = $webhookCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataWebhookFactory = $dataWebhookFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Save
     *
     * @param WebhookInterface $webhook
     * @return WebhookInterface
     * @throws CouldNotSaveException
     */
    public function save(
        WebhookInterface $webhook
    ) {
        $webhookData = $this->extensibleDataObjectConverter->toNestedArray(
            $webhook,
            [],
            WebhookInterface::class
        );
        $webhookModel = $this->webhookFactory->create()->setData($webhookData);
        try {
            $this->resource->save($webhookModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the webhook: %1',
                $exception->getMessage()
            ));
        }
        return $webhookModel->getDataModel();
    }

    /**
     * Get function
     * @param $webhookId
     * @return WebhookInterface
     * @throws NoSuchEntityException
     */
    public function get($webhookId)
    {
        $webhook = $this->webhookFactory->create();
        $this->resource->load($webhook, $webhookId);
        if (!$webhook->getId()) {
            throw new NoSuchEntityException(__('webhook with id "%1" does not exist.', $webhookId));
        }
        return $webhook->getDataModel();
    }

    /**
     * GetList
     *
     * @param SearchCriteriaInterface $criteria
     * @return WebhookSearchResultsInterface
     */
    public function getList(
        SearchCriteriaInterface $criteria
    ) {
        $collection = $this->webhookCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            WebhookInterface::class
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
     * Delete
     *
     * @param WebhookInterface $webhook
     * @return true
     * @throws CouldNotDeleteException
     */
    public function delete(
        WebhookInterface $webhook
    ) {
        try {
            $webhookModel = $this->webhookFactory->create();
            $this->resource->load($webhookModel, $webhook->getWebhookId());
            $this->resource->delete($webhookModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the webhook: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * DeleteById
     *
     * @param $webhookId
     * @return true
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($webhookId)
    {
        return $this->delete($this->get($webhookId));
    }
}
