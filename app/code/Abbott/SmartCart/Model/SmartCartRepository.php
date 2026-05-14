<?php

namespace Abbott\SmartCart\Model;

use Abbott\SmartCart\Model\ResourceModel\SmartCart\Collection;
use Abbott\SmartCart\Model\ResourceModel\SmartCart\CollectionFactory;
use Abbott\SmartCart\Api\Data\SmartCartInterface;
use Abbott\SmartCart\Api\SmartCartRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;

class SmartCartRepository implements SmartCartRepositoryInterface
{
    /**
     * @var CollectionFactory
     */
    private $smartCartCollectionFactory;

    /**
     * SmartCartRepository constructor.
     *
     * @param CollectionFactory $smartCartCollectionFactory
     */
    public function __construct(CollectionFactory $smartCartCollectionFactory)
    {

        $this->smartCartCollectionFactory = $smartCartCollectionFactory;
    }

    /**
     * GetSmartCartByCode
     *
     * @param $code
     * @param $storeId
     * @param $isActive
     * @return SmartCartInterface|DataObject
     * @throws NoSuchEntityException
     */
    public function getSmartCartByCode($code, $storeId = null, $isActive = true)
    {
        /** @var Collection $smartCartCollection */
        $smartCartCollection = $this->smartCartCollectionFactory->create();
        $smartCartCollection->addFieldToFilter("code", $code);
        $smartCartCollection->addFieldToFilter("store_id", $storeId);
        $smartCartCollection->addFieldToFilter("is_active", $isActive);
        if ($smartCartCollection->getSize() < 1) {
            throw new NoSuchEntityException(__("We could not find this smart cart"));
        }
        return $smartCartCollection->getFirstItem();
    }
}
