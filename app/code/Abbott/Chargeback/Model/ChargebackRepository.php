<?php

namespace Abbott\Chargeback\Model;

use Abbott\Chargeback\Api\Data\ChargebackInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Exception\CouldNotSaveException;
use Abbott\Chargeback\Model\ResourceModel\Chargeback\CollectionFactory as ChargebackCollectionFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Abbott\Chargeback\Api\ChargebackRepositoryInterface;
use Abbott\Chargeback\Api\Data\ChargebackSearchResultsInterfaceFactory;
use Abbott\Chargeback\Model\ResourceModel\Chargeback as ResourceChargeback;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;

class ChargebackRepository implements ChargebackRepositoryInterface
{
    /**
     * @var ChargebackSearchResultsInterfaceFactory
     */
    protected ChargebackSearchResultsInterfaceFactory $searchResultsFactory;

    /**
     * @var JoinProcessorInterface
     */
    protected JoinProcessorInterface $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private CollectionProcessorInterface $collectionProcessor;

    /**
     * @var ChargebackCollectionFactory
     */
    protected ChargebackCollectionFactory $chargebackCollectionFactory;

    /**
     * @var ResourceChargeback
     */
    protected ResourceChargeback $resource;

    /**
     * @var ChargebackFactory
     */
    protected ChargebackFactory $chargebackFactory;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected ExtensibleDataObjectConverter $extensibleDataObjectConverter;

    /**
     * @param ResourceChargeback $resource
     * @param ChargebackFactory $chargebackFactory
     * @param ChargebackCollectionFactory $chargebackCollectionFactory
     * @param ChargebackSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceChargeback $resource,
        ChargebackFactory $chargebackFactory,
        ChargebackCollectionFactory $chargebackCollectionFactory,
        ChargebackSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->chargebackFactory = $chargebackFactory;
        $this->chargebackCollectionFactory = $chargebackCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * @inheritdoc
     */
    public function save(
        ChargebackInterface $chargeback
    ) {
        $chargebackData = $this->extensibleDataObjectConverter->toNestedArray(
            $chargeback,
            [],
            ChargebackInterface::class
        );

        $chargebackModel = $this->chargebackFactory->create()->setData($chargebackData);

        try {
            $this->resource->save($chargebackModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the chargeback: %1',
                $exception->getMessage()
            ));
        }
        return $chargebackModel->getDataModel();
    }

    /**
     * @inheritdoc
     */
    public function get($chargebackId)
    {
        $chargeback = $this->chargebackFactory->create();
        $this->resource->load($chargeback, $chargebackId);
        if (!$chargeback->getId()) {
            throw new NoSuchEntityException(__('Chargeback with id "%1" does not exist.', $chargebackId));
        }
        return $chargeback->getDataModel();
    }

    /**
     * @inheritdoc
     */
    public function getList(
        SearchCriteriaInterface $criteria
    ) {
        $collection = $this->chargebackCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            ChargebackInterface::class
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
     * @inheritdoc
     */
    public function delete(
        ChargebackInterface $chargeback
    ) {
        try {
            $chargebackModel = $this->chargebackFactory->create();
            $this->resource->load($chargebackModel, $chargeback->getChargebackId());
            $this->resource->delete($chargebackModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Chargeback: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($chargebackId)
    {
        return $this->delete($this->get($chargebackId));
    }
}
