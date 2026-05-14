<?php

namespace Abbott\GlucernaOrders\Model;

use Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Abbott\GlucernaOrders\Api\Data\ManagesubscriptionInterface;

class Managesubscription extends \Magento\Framework\Model\AbstractModel
{
    protected $dataObjectHelper;

    protected $_eventPrefix = 'abbott_glucernaorders_collection';
    protected $managesubscriptionDataFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ManagesubscriptionInterfaceFactory $managesubscriptionDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Abbott\GlucernaOrders\Model\ResourceModel\Managesubscription $resource
     * @param \Abbott\GlucernaOrders\Model\ResourceModel\Managesubscription\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ManagesubscriptionInterfaceFactory $managesubscriptionDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Abbott\GlucernaOrders\Model\ResourceModel\Managesubscription $resource,
        \Abbott\GlucernaOrders\Model\ResourceModel\Managesubscription\Collection $resourceCollection,
        array $data = []
    ) {
        $this->managesubscriptionDataFactory = $managesubscriptionDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve managesubscription model with managesubscription data
     * @return ManagesubscriptionInterface
     */
    public function getDataModel()
    {
        $managesubscriptionData = $this->getData();

        $managesubscriptionDataObject = $this->managesubscriptionDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $managesubscriptionDataObject,
            $managesubscriptionData,
            ManagesubscriptionInterface::class
        );

        return $managesubscriptionDataObject;
    }
}
