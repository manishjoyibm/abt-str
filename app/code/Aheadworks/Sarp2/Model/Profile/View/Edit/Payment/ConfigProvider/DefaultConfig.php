<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider;

use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Form\FormKey;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Magento\Framework\UrlInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Info\Amount as InfoAmount;

/**
 * Class DefaultConfig
 *
 * @package Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider
 */
class DefaultConfig implements ConfigProviderInterface
{
    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var InfoAmount
     */
    private $infoAmount;

    /**
     * @param HttpContext $httpContext
     * @param FormKey $formKey
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param UrlInterface $urlBuilder
     * @param InfoAmount $infoAmount
     */
    public function __construct(
        HttpContext $httpContext,
        FormKey $formKey,
        StoreManagerInterface $storeManager,
        Registry $registry,
        UrlInterface $urlBuilder,
        InfoAmount $infoAmount
    ) {
        $this->httpContext = $httpContext;
        $this->formKey = $formKey;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->urlBuilder = $urlBuilder;
        $this->infoAmount = $infoAmount;
    }

    /**
     * Return configuration array
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getConfig()
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();

        $output['formKey'] = $this->formKey->getFormKey();
        $output['isCustomerLoggedIn'] = $this->isCustomerLoggedIn();
        $output['storeCode'] = $store->getCode();
        $output['profileId'] = $this->getProfile()->getProfileId();
        $output['savePaymentUrl'] = $this->getSavePaymentUrl();
        $output['quoteData'] = [];
        $output['totalsData'] = [
            'base_grand_total' => $this->infoAmount->getAmount(),
            'base_currency_code' => $store->getBaseCurrencyCode(),
            'quote_currency_code' => $store->getCurrentCurrencyCode()
        ];
        return $output;
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    private function isCustomerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * Get profile
     *
     * @return ProfileInterface
     */
    private function getProfile()
    {
        return $this->registry->registry('profile');
    }

    /**
     * Retrieve save payment url
     *
     * @return string
     */
    public function getSavePaymentUrl()
    {
        return $this->urlBuilder->getUrl(
            'aw_sarp2/profile_edit/savePayment',
            ['profile_id' => $this->getProfile()->getProfileId()]
        );
    }
}
