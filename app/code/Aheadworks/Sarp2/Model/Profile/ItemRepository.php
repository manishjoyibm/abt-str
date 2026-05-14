<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterfaceFactory;
use Aheadworks\Sarp2\Api\ProfileItemRepositoryInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Item as ItemResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ItemRepository
 * @package Aheadworks\Sarp2\Model\Profile
 */
class ItemRepository implements ProfileItemRepositoryInterface
{
    /**
     * @var ProfileItemInterface[]
     */
    private $instances = [];

    /**
     * @var ItemResource
     */
    private $resource;

    /**
     * @var ProfileItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * @param ItemResource $resource
     * @param ProfileItemInterfaceFactory $itemFactory
     */
    public function __construct(
        ItemResource $resource,
        ProfileItemInterfaceFactory $itemFactory
    ) {
        $this->resource = $resource;
        $this->itemFactory = $itemFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ProfileItemInterface $item)
    {
        try {
            if ($item->getParentItem()) {
                $item->setParentItemId($item->getParentItem()->getItemId());
            }
            $this->resource->save($item);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        $itemId = $item->getItemId();
        unset($this->instances[$itemId]);
        return $this->get($itemId);
    }

    /**
     * {@inheritdoc}
     */
    public function get($itemId)
    {
        if (!isset($this->instances[$itemId])) {
            /** @var ProfileItemInterface $item */
            $item = $this->itemFactory->create();
            $this->resource->load($item, $itemId);
            if (!$item->getItemId()) {
                throw NoSuchEntityException::singleField('itemId', $itemId);
            }
            $this->instances[$itemId] = $item;
        }
        return $this->instances[$itemId];
    }
}
