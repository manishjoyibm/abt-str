<?php

namespace Abbott\ProgressiveDiscount\Model;

use Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesInterfaceFactory;
use Abbott\ProgressiveDiscount\Api\Data\ManageDiscountCodesInterface;
use Magento\Framework\Api\DataObjectHelper;

class ManageDiscountCodes extends \Magento\Framework\Model\AbstractModel
{
    protected $dataObjectHelper;

    protected $managediscountcodesDataFactory;

    protected $_eventPrefix = 'manage_discount_rules';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ManageDiscountCodesInterfaceFactory $managediscountcodesDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Abbott\ProgressiveDiscount\Model\ResourceModel\ManageDiscountCodes $resource
     * @param \Abbott\ProgressiveDiscount\Model\ResourceModel\ManageDiscountCodes\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ManageDiscountCodesInterfaceFactory $managediscountcodesDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Abbott\ProgressiveDiscount\Model\ResourceModel\ManageDiscountCodes $resource,
        \Abbott\ProgressiveDiscount\Model\ResourceModel\ManageDiscountCodes\Collection $resourceCollection,
        array $data = []
    ) {
        $this->managediscountcodesDataFactory = $managediscountcodesDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve managediscountcodes model with managediscountcodes data
     * @return ManageDiscountCodesInterface
     */
    public function getDataModel()
    {
        $managediscountcodesData = $this->getData();

        $managediscountcodesDataObject = $this->managediscountcodesDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $managediscountcodesDataObject,
            $managediscountcodesData,
            ManageDiscountCodesInterface::class
        );

        return $managediscountcodesDataObject;
    }
}
