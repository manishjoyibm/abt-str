<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment\Action\Exception;

use Aheadworks\Sarp2\Engine\Exception\PaymentException;

/**
 * Interface HandlerInterface
 * @package Aheadworks\Sarp2\Engine\Payment\Action\Exception
 */
interface HandlerInterface
{
    /**
     * Handle exception
     *
     * @param \Exception $exception
     * @return PaymentException
     */
    public function handle($exception);
}
