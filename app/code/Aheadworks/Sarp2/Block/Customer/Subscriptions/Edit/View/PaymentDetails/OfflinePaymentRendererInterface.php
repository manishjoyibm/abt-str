<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails;

/**
 * Interface OfflinePaymentRendererInterface
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails
 */
interface OfflinePaymentRendererInterface
{
    /**
     * Can render
     *
     * @param string $method
     * @return boolean
     */
    public function canRender($method);

    /**
     * Render method details
     *
     * @return string
     */
    public function render();
}
