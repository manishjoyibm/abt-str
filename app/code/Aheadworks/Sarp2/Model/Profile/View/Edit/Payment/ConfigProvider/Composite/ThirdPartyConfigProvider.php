<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider\Composite;

use Magento\Framework\ObjectManagerInterface;
use Aheadworks\Sarp2\Model\ThirdPartyModule\Manager;
use Magento\Checkout\Model\ConfigProviderInterface;
use Aheadworks\Sarp2\Model\Payment\Ach\ConfigProvider as AchConfigProvider;

/**
 * Class ThirdPartyConfigProvider
 *
 * @package Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider\Composite
 */
class ThirdPartyConfigProvider
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Manager
     */
    private $thirdPartyModuleManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Manager $thirdPartyModuleManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Manager $thirdPartyModuleManager
    ) {
        $this->objectManager = $objectManager;
        $this->thirdPartyModuleManager = $thirdPartyModuleManager;
    }

    /**
     * @inheritdoc
     */
    public function getConfigProviders()
    {
        $providers = [];
        if ($this->thirdPartyModuleManager->isBamboraApacModuleEnabled()) {
            $providers[] = $this->getBamboraConfigProvider();
        }

        if ($this->thirdPartyModuleManager->isStripeModuleEnabled()) {
            $providers[] = $this->getStripeConfigProvider();
        }

        if ($this->thirdPartyModuleManager->isAuthorizenetCardinalModuleEnabled()) {
            $providers[] = $this->getAuthorizenetCardinalConfigProvider();
        }

        if ($this->thirdPartyModuleManager->isBraintreeModuleEnabled()) {
            $providers[] = $this->getBraintreeConfigProvider();
            $providers[] = $this->getBraintreePaypalConfigProvider();
        }

        if ($this->thirdPartyModuleManager->isBraintreeAchPaymentExist()) {
            $providers[] = $this->getBraintreeAchConfigProvider();
        }

        return $providers;
    }

    /**
     * Get Bambora config provider
     *
     * @return \Aheadworks\BamboraApac\Model\Ui\ConfigProvider
     */
    private function getBamboraConfigProvider()
    {
        return $this->objectManager->get(\Aheadworks\BamboraApac\Model\Ui\ConfigProvider::class);
    }

    /**
     * Get Stripe payments config provider
     *
     * @return ConfigProviderInterface
     */
    private function getStripeConfigProvider()
    {
        return $this->objectManager->get(\StripeIntegration\Payments\Model\Ui\ConfigProvider::class);
    }

    /**
     * Get Authorizenet Cardinal config provider
     *
     * @return ConfigProviderInterface
     */
    private function getAuthorizenetCardinalConfigProvider()
    {
        return $this->objectManager->get(\Magento\AuthorizenetCardinal\Model\Checkout\ConfigProvider::class);
    }

    /**
     * Get Braintree config provider
     *
     * @return ConfigProviderInterface
     */
    private function getBraintreeConfigProvider()
    {
        return $this->objectManager->get(\PayPal\Braintree\Model\Ui\ConfigProvider::class);
    }

    /**
     * Get Braintree Paypal config provider
     *
     * @return ConfigProviderInterface
     */
    private function getBraintreePaypalConfigProvider()
    {
        return $this->objectManager->get(\PayPal\Braintree\Model\Ui\PayPal\ConfigProvider::class);
    }

    /**
     * Get Braintree Ach config provider
     *
     * @return ConfigProviderInterface
     */
    private function getBraintreeAchConfigProvider()
    {
        return $this->objectManager->get(AchConfigProvider::class);
    }
}
