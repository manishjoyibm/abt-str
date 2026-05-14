<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment\Action\Exception\Resolver;

use Aheadworks\Sarp2\Engine\Payment\Action\Exception\HandlerInterface;

/**
 * Class ResolveStrategyInterface
 * @package Aheadworks\Sarp2\Engine\Payment\Action\Exception\Resolver
 */
interface ResolveStrategyInterface
{
    /**
     * Get exception handler
     *
     * @param \Exception $exception
     * @return HandlerInterface|null
     */
    public function resolve($exception);
}
