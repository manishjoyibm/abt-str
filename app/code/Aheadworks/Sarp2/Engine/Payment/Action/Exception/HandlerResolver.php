<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment\Action\Exception;

use Aheadworks\Sarp2\Engine\Payment\Action\Exception\Resolver\ResolveStrategyInterface;

/**
 * Class HandlerResolver
 * @package Aheadworks\Sarp2\Engine\Payment\Action\Exception
 */
class HandlerResolver
{
    /**
     * @var ResolveStrategyInterface[]
     */
    private $strategyPool;

    /**
     * @param array $strategyPool
     */
    public function __construct(array $strategyPool = [])
    {
        $this->strategyPool = $strategyPool;
    }

    /**
     * Get exception handler for specified exception
     *
     * @param \Exception $exception
     * @param string $paymentMethod
     * @return HandlerInterface|null
     */
    public function getHandler($exception, $paymentMethod)
    {
        if (isset($this->strategyPool[$paymentMethod])
            && $this->strategyPool[$paymentMethod] instanceof ResolveStrategyInterface
        ) {
            return $this->strategyPool[$paymentMethod]->resolve($exception);
        }
        return null;
    }
}
