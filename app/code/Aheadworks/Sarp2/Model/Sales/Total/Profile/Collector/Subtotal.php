<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Sales\Total\GroupInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\CollectorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Grand\Summator;
use Magento\Framework\DataObject\Factory;

/**
 * Class Subtotal
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector
 */
class Subtotal implements CollectorInterface
{
    /**
     * @var GroupInterface
     */
    private $totalsGroup;

    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @var Summator
     */
    private $grandSummator;

    /**
     * @param GroupInterface $totalsGroup
     * @param Factory $dataObjectFactory
     * @param Summator $grandSummator
     */
    public function __construct(
        GroupInterface $totalsGroup,
        Factory $dataObjectFactory,
        Summator $grandSummator
    ) {
        $this->totalsGroup = $totalsGroup;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->grandSummator = $grandSummator;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ProfileInterface $profile)
    {
        $baseSubtotal = 0;

        foreach ($profile->getItems() as $item) {
            if (!$item->getParentItem()) {
                $itemBasePrice = $this->totalsGroup->getItemPrice($item, true);
                $baseRowTotal = $itemBasePrice * $item->getQty();
                $totalsDetails = $this->dataObjectFactory->create(
                    [
                        'row_total' => $baseRowTotal,
                        'price' => $itemBasePrice
                    ]
                );
                $this->totalsGroup->getPopulator(ProfileItemInterface::class)->populate($item, $totalsDetails);

                if ($item->hasChildItems()) {
                    foreach ($item->getChildItems() as &$child) {
                        $this->totalsGroup->getPopulator(ProfileItemInterface::class)->populate($child, $totalsDetails);
                    }
                }

                $baseSubtotal += $baseRowTotal;
            }
        }

        $this->totalsGroup->getPopulator(ProfileInterface::class)
            ->populate(
                $profile,
                $this->dataObjectFactory->create(['subtotal' => $baseSubtotal])
            );
        $this->grandSummator->setAmount(
            $this->totalsGroup->getCode() . '_subtotal',
            $baseSubtotal
        );
    }
}
