<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Adminhtml\Product\SubscriptionOptions;

/**
 * Class Notice
 * @package Aheadworks\Sarp2\Block\Adminhtml\Product\SubscriptionOptions
 */
class Notice extends \Magento\Backend\Block\Template
{
    /**
     * Get subscription plans grid url
     *
     * @return string
     */
    public function getPlansUrl()
    {
        return $this->_urlBuilder->getUrl('aw_sarp2/plan/index');
    }
}
