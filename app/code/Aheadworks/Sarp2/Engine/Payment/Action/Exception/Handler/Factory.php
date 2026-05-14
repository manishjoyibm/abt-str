<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Engine\Payment\Action\Exception\Handler;

use Aheadworks\Sarp2\Engine\Payment\Action\Exception\HandlerInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Factory
 * @package Aheadworks\Sarp2\Engine\Payment\Action\Exception\Handler
 */
class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create exception handler instance
     *
     * @param string $className
     * @param array $data
     * @return HandlerInterface
     */
    public function create($className, $data = [])
    {
        $instance = $this->objectManager->create($className, $data);
        if (!$instance instanceof HandlerInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . HandlerInterface::class
            );
        }
        return $instance;
    }
}
