<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Quote\Total;

use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription;
use Magento\Framework\DataObject\Copy;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Api\Data\TotalsItemInterface;
use Magento\Quote\Api\Data\TotalsItemExtensionFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;

/**
 * Class Modifier
 * @package Aheadworks\Sarp2\Model\Quote\Total
 */
class Modifier
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var IsSubscription
     */
    private $isSubscriptionChecker;

    /**
     * @var TotalsItemExtensionFactory
     */
    private $totalsItemExtensionFactory;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param IsSubscription $isSubscriptionChecker
     * @param TotalsItemExtensionFactory $totalsItemExtensionFactory
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        IsSubscription $isSubscriptionChecker,
        TotalsItemExtensionFactory $totalsItemExtensionFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->isSubscriptionChecker = $isSubscriptionChecker;
        $this->totalsItemExtensionFactory = $totalsItemExtensionFactory;
    }

    /**
     * Modify totals data according to new meanings of mixed cart totals
     *
     * @param TotalsInterface $totals
     * @param int $cartId
     * @return TotalsInterface
     */
    public function modify(TotalsInterface $totals, $cartId)
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->get($cartId);
        foreach ($totals->getItems() as $item) {
            $itemId = $item->getItemId();
            $quoteItem = $quote->getItemById($itemId);
            $this->modifyItem($item, $quoteItem);
        }
        return $totals;
    }

    /**
     * Modify totals item
     *
     * @param TotalsItemInterface $item
     * @param Item $quoteItem
     * @return TotalsItemInterface
     */
    private function modifyItem(&$item, $quoteItem)
    {
        $isSubscriptionItem = $this->isSubscriptionChecker->check($quoteItem);

        $totalsItemExtension = $item->getExtensionAttributes();
        if ($totalsItemExtension === null) {
            $totalsItemExtension = $this->totalsItemExtensionFactory->create();
        }
        $totalsItemExtension->setAwSarpIsSubscription($isSubscriptionItem);
        $item->setExtensionAttributes($totalsItemExtension);

        return $item;
    }
}
