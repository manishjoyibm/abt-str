<?php

namespace Abbott\Chargeback\Model;

use Abbott\Chargeback\Api\Data\ChargebackInterface;
use Abbott\Chargeback\Api\Data\ChargebackInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Chargeback extends \Magento\Framework\Model\AbstractModel
{
    protected $dataObjectHelper;

    protected $chargebackDataFactory;

    protected $_eventPrefix = 'abbott_chargeback_log';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ChargebackInterfaceFactory $chargebackDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Abbott\Chargeback\Model\ResourceModel\Chargeback $resource
     * @param \Abbott\Chargeback\Model\ResourceModel\Chargeback\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ChargebackInterfaceFactory $chargebackDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Abbott\Chargeback\Model\ResourceModel\Chargeback $resource,
        \Abbott\Chargeback\Model\ResourceModel\Chargeback\Collection $resourceCollection,
        array $data = []
    ) {
        $this->chargebackDataFactory = $chargebackDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve chargeback model with chargeback data
     * @return ChargebackInterface
     */
    public function getDataModel()
    {
        $chargebackData = $this->getData();

        $chargebackDataObject = $this->chargebackDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $chargebackDataObject,
            $chargebackData,
            ChargebackInterface::class
        );

        return $chargebackDataObject;
    }
}
