<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Quote\Item;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterfaceFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Framework\DataObject\Copy;

/**
 * Class ToProfileItem
 * @package Aheadworks\Sarp2\Model\Quote\Item
 */
class ToProfileItem
{
    /**
     * @var ProfileItemInterfaceFactory
     */
    private $profileItemFactory;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @param ProfileItemInterfaceFactory $profileItemFactory
     * @param Copy $objectCopyService
     */
    public function __construct(
        ProfileItemInterfaceFactory $profileItemFactory,
        Copy $objectCopyService
    ) {
        $this->profileItemFactory = $profileItemFactory;
        $this->objectCopyService = $objectCopyService;
    }

    /**
     * Convert quote item to profile item
     *
     * @param Item $quoteItem
     * @param ProfileItemInterface|null $parentProfileItem
     * @return ProfileItemInterface
     */
    public function convert(Item $quoteItem, ProfileItemInterface &$parentProfileItem = null)
    {
        /** @var ProfileItemInterface $profileItem */
        $profileItem = $this->profileItemFactory->create();
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp2_convert_quote_item',
            'to_profile_item',
            $quoteItem,
            $profileItem
        );

        if (!$quoteItem->getParentItemId()) {
            $parentProfileItem = $profileItem;
            $profileItem->setParentItemId(null);
        } elseif ($parentProfileItem) {
            $profileItem->setParentItem($parentProfileItem);
        }
        $options = $quoteItem->getProductOrderOptions();
        if (!$options) {
            $options = $quoteItem->getProduct()
                ->getTypeInstance()
                ->getOrderOptions($quoteItem->getProduct());
        }
        $profileItem->setProductOptions($options);

        return $profileItem;
    }
}
