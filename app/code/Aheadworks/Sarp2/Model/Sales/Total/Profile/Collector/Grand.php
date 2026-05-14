<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Sales\Total\GroupInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\CollectorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Grand\Summator;
use Magento\Framework\DataObject\Factory;

/**
 * Class Grand
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector
 */
class Grand implements CollectorInterface
{
    /**
     * @var Summator
     */
    private $grandSummator;

    /**
     * @var GroupInterface
     */
    private $totalsGroup;

    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @param Summator $grandSummator
     * @param GroupInterface $totalsGroup
     * @param Factory $dataObjectFactory
     */
    public function __construct(
        Summator $grandSummator,
        GroupInterface $totalsGroup,
        Factory $dataObjectFactory
    ) {
        $this->grandSummator = $grandSummator;
        $this->totalsGroup = $totalsGroup;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ProfileInterface $profile)
    {
        $baseGrandTotal = $this->grandSummator->getSum($this->totalsGroup->getCode());
        $this->totalsGroup->getPopulator(ProfileInterface::class)
            ->populate(
                $profile,
                $this->dataObjectFactory->create(['grand_total' => $baseGrandTotal])
            );
    }
}
