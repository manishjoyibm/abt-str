<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Exception;

use Aheadworks\Sarp2\Api\Exception\OperationIsNotSupportedExceptionInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class OperationIsNotSupportedException
 * @package Aheadworks\Sarp2\Engine\Exception
 */
class OperationIsNotSupportedException extends LocalizedException implements OperationIsNotSupportedExceptionInterface
{
}
