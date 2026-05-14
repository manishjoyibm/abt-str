<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment\Action\Exception\Resolver\Type;

use Aheadworks\Sarp2\Engine\Payment\Action\Exception\Handler\Braintree\DefaultHandler as Handler;
use Aheadworks\Sarp2\Engine\Payment\Action\Exception\Handler\Factory;
use Aheadworks\Sarp2\Engine\Payment\Action\Exception\Resolver\ResolveStrategyInterface;

/**
 * Class Braintree
 * @package Aheadworks\Sarp2\Engine\Payment\Action\Exception\Resolver\Type
 */
class Braintree implements ResolveStrategyInterface
{
    /**
     * @var Factory
     */
    private $handlerFactory;

    /**
     * @param Factory $handlerFactory
     */
    public function __construct(Factory $handlerFactory)
    {
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($exception)
    {
        $exceptionClass = get_class($exception);
        return $this->handlerFactory->create(
            Handler::class,
            ['message' => 'Exception ' . $exceptionClass . ' has been raised.']
        );
    }
}
