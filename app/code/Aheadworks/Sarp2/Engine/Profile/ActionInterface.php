<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Profile;

use Magento\Framework\DataObject;

/**
 * Interface ActionInterface
 * @package Aheadworks\Sarp2\Engine\Profile
 */
interface ActionInterface
{
    /**#@+
     * Action types
     * @var string
     */
    const ACTION_TYPE_CHANGE_STATUS = 'change_status';
    const ACTION_TYPE_CHANGE_ADDRESS = 'change_address';
    const ACTION_TYPE_CHANGE_PLAN = 'change_plan';
    const ACTION_TYPE_CHANGE_NEXT_PAYMENT_DATE = 'change_next_payment_date';
    const ACTION_TYPE_CHANGE_PAYMENT_INFORMATION = 'change_payment_information';
    /**#@-*/

    /**
     * Get action type
     *
     * @return string
     */
    public function getType();

    /**
     * Get action data
     *
     * @return DataObject
     */
    public function getData();
}
