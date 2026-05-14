<?php

namespace Abbott\Hartehanks\Model;

use Abbott\Hartehanks\Api\Data\HarteHankInterfaceFactory;
use Abbott\Hartehanks\Api\Data\HarteHankInterface;
use Magento\Framework\Api\DataObjectHelper;

class HarteHank extends \Magento\Framework\Model\AbstractModel
{
    protected $hartehankDataFactory;

    protected $dataObjectHelper;

    protected $_eventPrefix = 'abbott_hartehanks_hartehank';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param HarteHankInterfaceFactory $hartehankDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Abbott\Hartehanks\Model\ResourceModel\HarteHank $resource
     * @param \Abbott\Hartehanks\Model\ResourceModel\HarteHank\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        HarteHankInterfaceFactory $hartehankDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Abbott\Hartehanks\Model\ResourceModel\HarteHank $resource,
        \Abbott\Hartehanks\Model\ResourceModel\HarteHank\Collection $resourceCollection,
        array $data = []
    ) {
        $this->hartehankDataFactory = $hartehankDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve hartehank model with hartehank data
     * @return HarteHankInterface
     */
    public function getDataModel()
    {
        $hartehankData = $this->getData();

        $hartehankDataObject = $this->hartehankDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $hartehankDataObject,
            $hartehankData,
            HarteHankInterface::class
        );

        return $hartehankDataObject;
    }
}
