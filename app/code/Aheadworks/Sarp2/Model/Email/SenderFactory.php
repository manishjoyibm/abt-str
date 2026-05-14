<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Email;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class SenderFactory
 * @package Aheadworks\Sarp2\Model\Email
 */
class SenderFactory
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
     * Create email sender instance
     *
     * @param string $className
     * @return SenderInterface
     */
    public function create($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof SenderInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . SenderInterface::class
            );
        }
        return $instance;
    }
}
