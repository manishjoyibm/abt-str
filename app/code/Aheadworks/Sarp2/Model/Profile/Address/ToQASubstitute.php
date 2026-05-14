<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Profile\Address;

use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Profile\Item\ToQAItemSubstitute;
use Aheadworks\Sarp2\Model\Profile\ToQuoteSubstitute;
use Aheadworks\Sarp2\Model\Quote\Substitute\Quote\Address as AddressSubstitute;
use Aheadworks\Sarp2\Model\Quote\Substitute\Quote\AddressFactory as AddressSubstituteFactory;
use Aheadworks\Sarp2\Model\Sales\CopySelf;
use Magento\Framework\DataObject\Copy;

/**
 * Class ToQASubstitute
 * @package Aheadworks\Sarp2\Model\Profile\Address
 */
class ToQASubstitute
{
    /**
     * @var AddressSubstituteFactory
     */
    private $addressSubstituteFactory;

    /**
     * @var ToQAItemSubstitute
     */
    private $toAddressItemSubstitute;

    /**
     * @var ToQuoteSubstitute
     */
    private $toQuoteSubstitute;

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
     * @param ToQAItemSubstitute $toAddressItemSubstitute
     * @param ToQuoteSubstitute $toQuoteSubstitute
     * @param Copy $objectCopyService
     * @param CopySelf $selfCopyService
     */
    public function __construct(
        AddressSubstituteFactory $addressSubstituteFactory,
        ToQAItemSubstitute $toAddressItemSubstitute,
        ToQuoteSubstitute $toQuoteSubstitute,
        Copy $objectCopyService,
        CopySelf $selfCopyService
    ) {
        $this->addressSubstituteFactory = $addressSubstituteFactory;
        $this->toAddressItemSubstitute = $toAddressItemSubstitute;
        $this->toQuoteSubstitute = $toQuoteSubstitute;
        $this->objectCopyService = $objectCopyService;
        $this->selfCopyService = $selfCopyService;
    }

    /**
     * Convert profile address into quote address substitute
     *
     * @param ProfileAddressInterface $profileAddress
     * @param string $totalsGroupCode
     * @param ProfileInterface $profile
     * @param ProfileItemInterface[]|null $profileItems
     * @param array $data
     * @return AddressSubstitute
     */
    public function convert(
        $profileAddress,
        $totalsGroupCode,
        $profile,
        $profileItems = null,
        array $data = []
    ) {
        /** @var AddressSubstitute $addressSubstitute */
        $addressSubstitute = $this->addressSubstituteFactory->create();
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp2_convert_profile_address',
            'to_quote_address_substitute',
            $profileAddress,
            $addressSubstitute
        );

        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp2_convert_profile',
            'to_quote_address_substitute',
            $profile,
            $addressSubstitute
        );
        $this->objectCopyService->copyFieldsetToTarget(
            'aw_sarp2_convert_profile',
            'to_quote_address_substitute_' . $totalsGroupCode,
            $profile,
            $addressSubstitute
        );

        $this->selfCopyService->copyByMap(
            $addressSubstitute,
            [['base_subtotal', 'base_subtotal_with_discount']]
        );

        $quoteSubstitute = $this->toQuoteSubstitute->convert($profile, $totalsGroupCode);
        $allAddressItems = $this->toQAItemSubstitutes(
            $profileItems ? : $profile->getItems(),
            $totalsGroupCode,
            ['address' => $addressSubstitute, 'quote' => $quoteSubstitute]
        );
        $addressSubstitute->addData(
            array_merge(
                [
                    'all_items' => $allAddressItems,
                    'quote' => $quoteSubstitute
                ],
                $data
            )
        );

        return $addressSubstitute;
    }

    /**
     * Convert profile items to address items substitutes
     *
     * @param ProfileItemInterface[] $profileItems
     * @param string $totalsGroupCode
     * @param array $data
     * @return array
     */
    private function toQAItemSubstitutes($profileItems, $totalsGroupCode, $data = [])
    {
        $items = [];
        foreach ($profileItems as $profileItem) {
            $itemId = $profileItem->getItemId();

            if (!isset($items[$itemId])) {
                $parentItemId = $profileItem->getParentItemId();
                if ($parentItemId && !isset($items[$parentItemId])) {
                    $items[$parentItemId] = $this->toAddressItemSubstitute->convert(
                        $profileItem->getParentItem(),
                        $totalsGroupCode,
                        array_merge($data, ['parent_item' => null])
                    );
                }
                $parentItem = isset($items[$parentItemId])
                    ? $items[$parentItemId]
                    : null;
                $items[$itemId] = $this->toAddressItemSubstitute->convert(
                    $profileItem,
                    $totalsGroupCode,
                    array_merge($data, ['parent_item' => $parentItem])
                );
            }
        }
        return array_values($items);
    }
}
