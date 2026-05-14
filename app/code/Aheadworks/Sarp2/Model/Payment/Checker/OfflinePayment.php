<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment\Checker;

/**
 * Class OfflinePayment
 * @package Aheadworks\Sarp2\Model\Payment\Checker
 */
class OfflinePayment
{
    /**
     * @var array
     */
    private $allowedMethods;

    /**
     * @param array $allowedMethods
     */
    public function __construct(
        array $allowedMethods = []
    ) {
        $this->allowedMethods = $allowedMethods;
    }

    /**
     * Check
     *
     * @param string $methodCode
     * @return bool
     */
    public function check($methodCode)
    {
        return in_array($methodCode, $this->allowedMethods);
    }
}
