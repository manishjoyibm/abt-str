<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Product\Subscription\Details\Config;

/**
 * Class ProviderPool
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Details\Config
 */
class ProviderPool
{
    /**
     * @var ProviderInterface[]
     */
    private $providerInstances = [];

    /**
     * @var array
     */
    private $providers = [];

    /**
     * @var Generic
     */
    private $defaultProvider;

    /**
     * @var ProviderFactory
     */
    private $providerFactory;

    /**
     * @param ProviderFactory $providerFactory
     * @param Generic $defaultProvider
     * @param array $providers
     */
    public function __construct(
        ProviderFactory $providerFactory,
        Generic $defaultProvider,
        array $providers = []
    ) {
        $this->providerFactory = $providerFactory;
        $this->defaultProvider = $defaultProvider;
        $this->providers = array_merge($this->providers, $providers);
    }

    /**
     * Get regular price config provider instance
     *
     * @param string $typeId
     * @return ProviderInterface
     * @throws \Exception
     */
    public function getConfigProvider($typeId)
    {
        if (!isset($this->providerInstances[$typeId])) {
            $this->providerInstances[$typeId] = isset($this->providers[$typeId])
                ? $this->providerFactory->create($this->providers[$typeId])
                : $this->defaultProvider;
        }
        return $this->providerInstances[$typeId];
    }
}
