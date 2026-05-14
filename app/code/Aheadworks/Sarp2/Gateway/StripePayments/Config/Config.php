<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Gateway\StripePayments\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface;
use StripeIntegration\Payments\Model\Config as StripeConfig;

/**
 * Class Config
 * @package Aheadworks\BamboraApac\Gateway\Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    /**#@+
     * API info data
     */
    const MODULE_NAME = 'Advanced Subscription Products for Magento 2';
    const MODULE_URL = 'https://ecommerce.aheadworks.com/magento-2-extensions/subscriptions-and-recurring-payments';
    /**#@-*/

    /**#@+
     * Constants for config path
     */
    const KEY_AW_SARP_CCTYPES_MAP = 'aw_sarp_cctypes_map';
    /**#@-*/

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var StripeConfig
     */
    private $config;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ObjectManagerInterface $objectManager
     * @param ModuleListInterface $moduleList
     * @param Json $serializer
     * @param null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ObjectManagerInterface $objectManager,
        ModuleListInterface $moduleList,
        Json $serializer,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->objectManager = $objectManager;
        $this->moduleList = $moduleList;
        $this->serializer = $serializer;
    }

    /**
     * Get api info
     *
     * @return array
     */
    public function getApiInfo()
    {

        $apiInfo = [
            'module_name' => self::MODULE_NAME,
            'module_version' => $this->getModuleVersion(),
            'module_url' => self::MODULE_URL,
        ];

        return $apiInfo;
    }

    /**
     * Get secret key
     *
     * @return string
     */
    public function getSecretKey()
    {
        /** @var StripeConfig $config */
        $config = $this->getConfig();

        return $config->getSecretKey();
    }

    /**
     * Get stripe params from order
     *
     * @param OrderInterface $order
     * @return array
     */
    public function getStripeParamsFromOrder($order)
    {
        /** @var StripeConfig $config */
        $config = $this->getConfig();

        $params = $config->getStripeParamsFrom($order);
        $params['metadata']['Module'] = self::MODULE_NAME . ' v' . $this->getModuleVersion();

        return $params;
    }

    /**
     * Retrieve map between Magento and Stripe card types
     *
     * @return array
     */
    public function getCcTypesMap()
    {
        $result = $this->serializer->unserialize($this->getValue(self::KEY_AW_SARP_CCTYPES_MAP));

        return is_array($result) ? $result : [];
    }

    /**
     * Get module version
     *
     * @return string
     */
    private function getModuleVersion()
    {
        $moduleInfo = $this->moduleList->getOne('Aheadworks_Sarp2');

        return $moduleInfo['setup_version'];
    }

    /**
     * Get Stripe config
     *
     * @return StripeConfig
     */
    private function getConfig()
    {
        if (!$this->config) {
            $this->config = $this->objectManager->create(StripeConfig::class);
        }

        return $this->config;
    }
}
