<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Plan\PostDataProcessor;

/**
 * Class Reminder
 *
 * @package Aheadworks\Sarp2\Model\Plan\PostDataProcessor
 */
class Reminder implements ProcessorInterface
{
    /**
     * @inheritdoc
     */
    public function prepareEntityData($data)
    {
        if (isset($data['use_default']['upcoming_billing_email_offset'])
            && (bool)$data['use_default']['upcoming_billing_email_offset']
        ) {
            $data['definition']['upcoming_billing_email_offset'] = null;
        }

        return $data;
    }
}
