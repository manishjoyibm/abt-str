<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector;

use Aheadworks\Sarp2\Model\Sales\Total\Merged\CollectorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector\Grand\Summator;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\Subject;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Grand
 * @package Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector
 */
class Grand implements CollectorInterface
{
    /**
     * @var Summator
     */
    private $grandSummator;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param Summator $grandSummator
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Summator $grandSummator,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->grandSummator = $grandSummator;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Subject $subject)
    {
        $baseGrandTotal = $this->grandSummator->getSum();
        $subject->getOrder()
            ->setBaseGrandTotal($baseGrandTotal)
            ->setGrandTotal($this->priceCurrency->convert($baseGrandTotal));
    }
}
