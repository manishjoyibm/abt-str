<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Payment;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface SamplerInterface
 * @package Aheadworks\Sarp2\Model\Payment
 */
interface SamplerInterface
{
    /**
     * Import payment data
     *
     * @param SamplerInfoInterface $info
     * @param array $data
     * @return SamplerInfoInterface
     * @throws LocalizedException
     */
    public function importPayment(SamplerInfoInterface $info, array $data);

    /**
     * Place payment method
     *
     * @param SamplerInfoInterface $info
     * @return $this
     */
    public function place(SamplerInfoInterface $info);

    /**
     * Revert payment method
     *
     * @param SamplerInfoInterface $info
     * @return $this
     */
    public function revert(SamplerInfoInterface $info);
}
