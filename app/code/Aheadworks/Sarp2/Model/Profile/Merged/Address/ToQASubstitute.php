<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Profile\Merged\Address;

use Aheadworks\Sarp2\Engine\Profile\PaymentInfoInterface;
use Aheadworks\Sarp2\Model\Profile\Item\ToQAItemSubstitute;
use Aheadworks\Sarp2\Model\Profile\Merged\Set\DataResolver;
use Aheadworks\Sarp2\Model\Quote\Substitute\Quote as QuoteSubstitute;
use Aheadworks\Sarp2\Model\Quote\Substitute\QuoteFactory as QuoteSubstituteFactory;
use Aheadworks\Sarp2\Model\Quote\Substitute\Quote\Address as AddressSubstitute;
use Aheadworks\Sarp2\Model\Quote\Substitute\Quote\AddressFactory as AddressSubstituteFactory;
use Aheadworks\Sarp2\Model\Quote\Substitute\Quote\Address\Item as ItemSubstitute;
use Aheadworks\Sarp2\Model\Sales\CopySelf;
use Magento\Framework\DataObject\Copy;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ToQASubstitute
 * @package Aheadworks\Sarp2\Model\Profile\Merged\Address
 */
class ToQASubstitute
{
    /**
     * @var AddressSubstituteFactory
     */
    private $addressSubstituteFactory;

    /**
     * @var QuoteSubstituteFactory
     */
    private $quoteSubstituteFactory;

    /**
     * @var DataResolver
     */
    private $dataResolver;

    /**
     * @var ToQAItemSubstitute
     */
    private $toAddressItemSubstitute;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @var CopySelf
     */
    private $selfCopyService;

    /**
     * @param AddressSubstituteFactory $addressSubstituteFactory
     * @param QuoteSubstituteFactory $quoteSubstituteFactory
     * @param DataResolver $dataResolver
     * @param ToQAItemSubstitute $toAddressItemSubstitute
     * @param StoreManagerInterface $storeManager
     * @param Copy $objectCopyService
     * @param CopySelf $selfCopyService
     */
    public function __construct(
        AddressSubstituteFactory $addressSubstituteFactory,
        QuoteSubstituteFactory $quoteSubstituteFactory,
        DataResolver $dataResolver,
        ToQAItemSubstitute $toAddressItemSubstitute,
        StoreManagerInterface $storeManager,
        Copy $objectCopyService,
        CopySelf $selfCopyService
    ) {
        $this->addressSubstituteFactory = $addressSubstituteFactory;
        $this->quoteSubstituteFactory = $quoteSubstituteFactory;
        $this->dataResolver = $dataResolver;
        $this->toAddressItemSubstitute = $toAddressItemSubstitute;
        $this->storeManager = $storeManager;
        $this->objectCopyService = $objectCopyService;
        $this->selfCopyService = $selfCopyService;
    }

    /**
     * Convert profiles to merged quote address substitute
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @param array $data
     * @return AddressSubstitute
     */
    public function convert($paymentsInfo, array $data = [])
    {
        /** @var AddressSubstitute $addressSubstitute */
        $addressSubstitute = $this->addressSubstituteFactory->create();
        $address = $this->dataResolver->getShippingAddress($paymentsInfo);
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp2_convert_profile_address',
            'to_quote_address_substitute',
            $address,
            $addressSubstitute
        );

        $storeId = $this->dataResolver->getStoreId($paymentsInfo);

        /** @var QuoteSubstitute $quoteSubstitute */
        $quoteSubstitute = $this->quoteSubstituteFactory->create();
        $quoteSubstitute->addData(
            [
                'store_id' => $storeId,
                'store' => $this->storeManager->getStore($storeId),
                'coupon_code' => null,
                'customer_group_id' => $this->dataResolver->getCustomerGroupId($paymentsInfo)
            ]
        );

        $allAddressItems = $this->toQAItemSubstitutes(
            $paymentsInfo,
            ['address' => $addressSubstitute, 'quote' => $quoteSubstitute]
        );
        $addressSubstitute->addData(
            array_merge(
                [
                    'all_items' => $allAddressItems,
                    'quote' => $quoteSubstitute
                ],
                $this->collectAmountsData($allAddressItems),
                $data
            )
        );

        return $addressSubstitute;
    }

    /**
     * Convert profiles to merged address items substitutes
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @param array $data
     * @return array
     */
    private function toQAItemSubstitutes($paymentsInfo, $data = [])
    {
        $items = [];
        foreach ($paymentsInfo as $info) {
            $paymentPeriod = $info->getPaymentPeriod();

            foreach ($info->getProfile()->getItems() as $profileItem) {
                $itemId = $profileItem->getItemId();

                if (!isset($items[$itemId])) {
                    $parentItemId = $profileItem->getParentItemId();
                    if ($parentItemId && !isset($items[$parentItemId])) {
                        $parent = $this->toAddressItemSubstitute->convert(
                            $profileItem->getParentItem(),
                            $paymentPeriod,
                            array_merge($data, ['parent_item' => null])
                        );
                        if (!$parent->getProduct()->getIsVirtual()) {
                            $items[$parentItemId] = $parent;
                        }
                    }
                    $parentItem = isset($items[$parentItemId])
                        ? $items[$parentItemId]
                        : null;
                    $item = $this->toAddressItemSubstitute->convert(
                        $profileItem,
                        $paymentPeriod,
                        array_merge($data, ['parent_item' => $parentItem])
                    );
                    if (!$item->getProduct()->getIsVirtual()) {
                        $items[$itemId] = $item;
                    }
                }
            }
        }
        return array_values($items);
    }

    /**
     * Collect address amounts data
     *
     * @param ItemSubstitute[] $items
     * @return array
     */
    private function collectAmountsData($items)
    {
        $baseSubtotal = 0;
        $baseSubtotalInclTax = 0;
        $baseVirtualAmount = 0;

        foreach ($items as $item) {
            $qty = $item->getQty();

            $baseSubtotal += $item->getRowTotal() * $qty;
            $baseSubtotalInclTax += $item->getRowTotalInclTax() * $qty;

            if ($item->getProduct()->getIsVirtual()) {
                $baseVirtualAmount += $baseSubtotal;
            }
        }

        return [
            'base_subtotal' => $baseSubtotal,
            'base_subtotal_total_incl_tax' => $baseSubtotalInclTax,
            'base_virtual_amount' => $baseVirtualAmount
        ];
    }
}
