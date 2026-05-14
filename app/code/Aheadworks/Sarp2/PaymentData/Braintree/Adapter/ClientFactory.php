<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\PaymentData\Braintree\Adapter;

use PayPal\Braintree\Gateway\Config\Config;
use PayPal\Braintree\Model\Adminhtml\Source\Environment;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class ClientFactory
 * @package Aheadworks\Sarp2\PaymentData\Braintree\Adapter
 */
class ClientFactory
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param Config $config
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Config $config,
        ObjectManagerInterface $objectManager
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * Create client instance
     *
     * @param int|null $storeId
     * @return Client
     */
    public function create($storeId = null)
    {
        return $this->objectManager->create(
            Client::class,
            [
                'config' => [
                    'merchant_id' => $this->config->getMerchantId($storeId),
                    'public_key' => $this->config->getValue(Config::KEY_PUBLIC_KEY, $storeId),
                    'private_key' => $this->config->getValue(Config::KEY_PRIVATE_KEY, $storeId),
                    'environment' => $this->config->getEnvironment($storeId) == Environment::ENVIRONMENT_PRODUCTION
                        ? Environment::ENVIRONMENT_PRODUCTION
                        : Environment::ENVIRONMENT_SANDBOX
                ]
            ]
        );
    }
}
