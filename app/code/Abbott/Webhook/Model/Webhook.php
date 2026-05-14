<?php

namespace Abbott\Webhook\Model;

use Abbott\Webhook\Api\Data\WebhookInterface;
use Abbott\Webhook\Api\Data\WebhookInterfaceFactory;
use Abbott\Webhook\Model\ResourceModel\Webhook\Collection;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class Webhook extends \Magento\Framework\Model\AbstractModel
{
    protected $dataObjectHelper;

    protected $_eventPrefix = 'apollo_webhook';
    protected $webhookDataFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param WebhookInterfaceFactory $webhookDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ResourceModel\Webhook $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context                                                $context,
        Registry                                               $registry,
        WebhookInterfaceFactory                                $webhookDataFactory,
        DataObjectHelper                                       $dataObjectHelper,
        ResourceModel\Webhook                                  $resource,
        Collection $resourceCollection,
        array                                                  $data = []
    ) {
        $this->webhookDataFactory = $webhookDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve webhook model with webhook data
     * @return WebhookInterface
     */
    public function getDataModel()
    {
        $webhookData = $this->getData();
        $webhookDataObject = $this->webhookDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $webhookDataObject,
            $webhookData,
            WebhookInterface::class
        );
        return $webhookDataObject;
    }
}
