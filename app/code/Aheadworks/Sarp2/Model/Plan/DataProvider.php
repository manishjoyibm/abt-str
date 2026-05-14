<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Plan;

use Aheadworks\Sarp2\Model\ResourceModel\Plan\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Plan\CollectionFactory;
use Aheadworks\Sarp2\Model\Plan;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class DataProvider
 * @package Aheadworks\Sarp2\Model\Plan
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $subscriptionPlanCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $subscriptionPlanCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $subscriptionPlanCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Plan $plan */
        foreach ($items as $plan) {
            $this->loadedData[$plan->getPlanId()] = $this->prepareFormData(
                $plan->getData()
            );
        }

        $data = $this->dataPersistor->get('aw_sarp2_plan');
        if (!empty($data)) {
            $plan = $this->collection->getNewEmptyItem();
            $plan->setData($data);
            $this->loadedData[$plan->getPlanId()] = $this->prepareFormData(
                $plan->getData()
            );
            $this->dataPersistor->clear('aw_sarp2_plan');
        }

        return $this->loadedData;
    }

    /**
     * Prepare form data
     *
     * @param array $data
     * @return array
     */
    private function prepareFormData($data)
    {
        if ($data['definition']['trial_total_billing_cycles'] == 0) {
            $data['definition']['trial_total_billing_cycles'] = null;
        }
        if ($data['trial_price_pattern_percent'] == 0) {
            $data['trial_price_pattern_percent'] = null;
        }
        return $data;
    }
}
