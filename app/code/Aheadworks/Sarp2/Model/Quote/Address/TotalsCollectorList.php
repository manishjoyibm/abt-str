<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Quote\Address;

use Aheadworks\Sarp2\Model\Quote\Address\Total\Replacer;
use Aheadworks\Sarp2\Model\Quote\Address\Total\ReplacerFactory;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote\Address\Total\CollectorFactory;
use Magento\Quote\Model\Quote\Address\TotalFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class TotalsCollectorList
 * @package Aheadworks\Sarp2\Model\Quote\Address
 */
class TotalsCollectorList
{
    /**
     * @var CollectorFactory
     */
    private $totalCollectorFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TotalFactory
     */
    private $totalFactory;

    /**
     * @var ReplacerFactory
     */
    private $totalReplacerFactory;

    /**
     * @var AbstractTotal[]
     */
    private $collectors;

    /**
     * @var array
     */
    private $totalCodes = [
        'subtotal',
        'discount',
        'shipping',
        'grand_total',
        'tax',
        'tax_subtotal',
        'tax_shipping',
        'weee',
        'weee_tax'
    ];

    /**
     * @param CollectorFactory $totalCollectorFactory
     * @param StoreManagerInterface $storeManager
     * @param TotalFactory $totalFactory
     * @param ReplacerFactory $totalReplacerFactory
     */
    public function __construct(
        CollectorFactory $totalCollectorFactory,
        StoreManagerInterface $storeManager,
        TotalFactory $totalFactory,
        ReplacerFactory $totalReplacerFactory
    ) {
        $this->totalCollectorFactory = $totalCollectorFactory;
        $this->storeManager = $storeManager;
        $this->totalFactory = $totalFactory;
        $this->totalReplacerFactory = $totalReplacerFactory;
    }

    /**
     * Get totals collector list
     *
     * @param int $storeId
     * @return AbstractTotal[]
     */
    public function getCollectors($storeId)
    {
        if (!$this->collectors) {
            $totalCollector = $this->totalCollectorFactory->create(
                ['store' => $this->storeManager->getStore($storeId)]
            );
            foreach ($totalCollector->getCollectors() as $code => $collector) {
                if (preg_match('/^aw_sarp2/', $code) || in_array($code, $this->totalCodes)) {
                    $this->collectors[$code] = $collector;
                } else {
                    /** @var Replacer $replacer */
                    $replacer = $this->totalReplacerFactory->create();
                    $replacer->setCode($code);
                    $this->collectors[$code] = $replacer;
                }
            }
        }
        return $this->collectors;
    }
}
