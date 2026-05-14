<?php

namespace Abbott\Hartehanks\Model;

use Abbott\Hartehanks\Api\Data\HhFindorderInterface;
use Abbott\Hartehanks\Api\Data\HhFindorderInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class HhFindorder extends \Magento\Framework\Model\AbstractModel
{
    protected $dataObjectHelper;

    protected $_eventPrefix = 'apollo_hartehank_findorder_log';

    protected $hhfindorderDataFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param HhFindorderInterfaceFactory $hhfindorderDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Abbott\Hartehanks\Model\ResourceModel\HhFindorder $resource
     * @param \Abbott\Hartehanks\Model\ResourceModel\HhFindorder\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        HhFindorderInterfaceFactory $hhfindorderDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Abbott\Hartehanks\Model\ResourceModel\HhFindorder $resource,
        \Abbott\Hartehanks\Model\ResourceModel\HhFindorder\Collection $resourceCollection,
        array $data = []
    ) {
        $this->hhfindorderDataFactory = $hhfindorderDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve hhfindorder model with hhfindorder data
     * @return HhFindorderInterface
     */
    public function getDataModel()
    {
        $hhfindorderData = $this->getData();

        $hhfindorderDataObject = $this->hhfindorderDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $hhfindorderDataObject,
            $hhfindorderData,
            HhFindorderInterface::class
        );

        return $hhfindorderDataObject;
    }
}
