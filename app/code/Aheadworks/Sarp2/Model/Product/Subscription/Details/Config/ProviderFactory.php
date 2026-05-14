<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Product\Subscription\Details\Config;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ProviderFactory
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Details\Config
 */
class ProviderFactory
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
     * Create subscription details config provider instance
     *
     * @param string $className
     * @return ProviderInterface
     */
    public function create($className)
    {
        $instance = $this->objectManager->create($className);
        if (!$instance instanceof ProviderInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . ProviderInterface::class
            );
        }
        return $instance;
    }
}
