<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Sales\Total;

use Magento\Framework\DataObject;

/**
 * Interface PopulatorInterface
 * @package Aheadworks\Sarp2\Model\Sales\Total
 */
interface PopulatorInterface
{
    /**#@+
     * Currency options
     */
    const CURRENCY_OPTION_USE_BASE = 'base';
    const CURRENCY_OPTION_USE_STORE = 'store';
    const CURRENCY_OPTION_CONVERT = 'convert';
    /**#@-*/

    /**
     * Populate entity with totals data
     *
     * @param object $entity
     * @param DataObject $totalsDetails
     * @param string $currencyOption
     * @return void
     */
    public function populate(
        $entity,
        DataObject $totalsDetails,
        $currencyOption = self::CURRENCY_OPTION_CONVERT
    );
}
