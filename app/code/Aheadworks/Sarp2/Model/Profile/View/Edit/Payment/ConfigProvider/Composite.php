<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider;

use Magento\Checkout\Model\ConfigProviderInterface;
use Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider\Composite\ThirdPartyConfigProvider;

/**
 * Class Composite
 *
 * @package Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider
 */
class Composite implements ConfigProviderInterface
{
    /**
     * @var ConfigProviderInterface[]
     */
    private $configProviders;

    /**
     * @var ThirdPartyConfigProvider
     */
    private $thirdPartConfigProvider;

    /**
     * @param ThirdPartyConfigProvider $thirdPartConfigProvider
     * @param array $configProviders
     */
    public function __construct(
        ThirdPartyConfigProvider $thirdPartConfigProvider,
        array $configProviders
    ) {
        $this->thirdPartConfigProvider = $thirdPartConfigProvider;
        $this->configProviders = $configProviders;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        $config = [];
        foreach ($this->configProviders as $configProvider) {
            $config = array_merge_recursive($config, $configProvider->getConfig());
        }
        $thirdPartyConfigProviders = $this->thirdPartConfigProvider->getConfigProviders();
        foreach ($thirdPartyConfigProviders as $configProvider) {
            $config = array_merge_recursive($config, $configProvider->getConfig());
        }
        return $config;
    }
}
