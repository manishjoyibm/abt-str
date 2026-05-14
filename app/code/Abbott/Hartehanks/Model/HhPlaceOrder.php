<?php

namespace Abbott\Hartehanks\Model;

use Abbott\Hartehanks\Api\Data\HhPlaceOrderInterface;
use Magento\Framework\Api\DataObjectHelper;
use Abbott\Hartehanks\Api\Data\HhPlaceOrderInterfaceFactory;

class HhPlaceOrder extends \Magento\Framework\Model\AbstractModel
{
    protected $dataObjectHelper;

    protected $hhplaceorderDataFactory;

    protected $_eventPrefix = 'abbott_hartehanks_hhplaceorder';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param HhPlaceOrderInterfaceFactory $hhplaceorderDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Abbott\Hartehanks\Model\ResourceModel\HhPlaceOrder $resource
     * @param \Abbott\Hartehanks\Model\ResourceModel\HhPlaceOrder\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        HhPlaceOrderInterfaceFactory $hhplaceorderDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Abbott\Hartehanks\Model\ResourceModel\HhPlaceOrder $resource,
        \Abbott\Hartehanks\Model\ResourceModel\HhPlaceOrder\Collection $resourceCollection,
        array $data = []
    ) {
        $this->hhplaceorderDataFactory = $hhplaceorderDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve hhplaceorder model with hhplaceorder data
     * @return HhPlaceOrderInterface
     */
    public function getDataModel()
    {
        $hhplaceorderData = $this->getData();

        $hhplaceorderDataObject = $this->hhplaceorderDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $hhplaceorderDataObject,
            $hhplaceorderData,
            HhPlaceOrderInterface::class
        );

        return $hhplaceorderDataObject;
    }
}
