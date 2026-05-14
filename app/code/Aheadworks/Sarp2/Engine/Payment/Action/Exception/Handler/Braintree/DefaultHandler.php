<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment\Action\Exception\Handler\Braintree;

use Aheadworks\Sarp2\Engine\Exception\PaymentExceptionFactory;
use Aheadworks\Sarp2\Engine\Payment\Action\Exception\HandlerInterface;

/**
 * Class DefaultHandler
 * @package Aheadworks\Sarp2\Engine\Payment\Action\Exception\Handler\Braintree
 */
class DefaultHandler implements HandlerInterface
{
    /**
     * @var PaymentExceptionFactory
     */
    private $exceptionFactory;

    /**
     * @var string
     */
    private $message;

    /**
     * @param PaymentExceptionFactory $exceptionFactory
     * @param string $message
     */
    public function __construct(
        PaymentExceptionFactory $exceptionFactory,
        $message = 'Something went wrong'
    ) {
        $this->exceptionFactory = $exceptionFactory;
        $this->message = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($exception)
    {
        return $this->exceptionFactory->create(
            [
                'phrase' => __($this->message),
                'cause' => $exception,
                'code' => $exception->getCode()
            ]
        );
    }
}
