<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Exception;

use Aheadworks\Sarp2\Api\Exception\CouldNotScheduleExceptionInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class CouldNotScheduleException
 * @package Aheadworks\Sarp2\Engine\Exception
 */
class CouldNotScheduleException extends LocalizedException implements CouldNotScheduleExceptionInterface
{
}
