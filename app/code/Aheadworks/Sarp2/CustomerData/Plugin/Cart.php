<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\CustomerData\Plugin;

use Aheadworks\Sarp2\CustomerData\Cart\DataProcessor;
use Magento\Checkout\CustomerData\Cart as CartData;

/**
 * Class Cart
 * @package Aheadworks\Sarp2\CustomerData\Plugin
 */
class Cart
{
    /**
     * @var DataProcessor
     */
    private $cartDataProcessor;

    /**
     * @param DataProcessor $cartDataProcessor
     */
    public function __construct(DataProcessor $cartDataProcessor)
    {
        $this->cartDataProcessor = $cartDataProcessor;
    }

    /**
     * @param CartData $subject
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionData(CartData $subject, array $data)
    {
        return $this->cartDataProcessor->process($data);
    }
}
