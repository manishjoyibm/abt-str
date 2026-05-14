<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment\Engine\Logger;

/**
 * Interface DataFormatterInterface
 * @package Aheadworks\Sarp2\Engine\Payment\Engine\Logger
 */
interface DataFormatterInterface
{
    /**
     * Log data parts delimiter
     */
    const PARTS_DELIMITER = ' | ';

    /**
     * Format log data
     *
     * @param object|array $subject
     * @return string
     */
    public function format($subject);
}
