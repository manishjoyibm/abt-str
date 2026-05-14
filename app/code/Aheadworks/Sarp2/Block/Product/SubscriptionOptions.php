<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Product;

use Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\ProviderPool as ConfigProviderPool;
use Aheadworks\Sarp2\Model\Product\Checker\IsSubscription;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Source\Frontend as SubscriptionOptionSource;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class SubscriptionOptions
 * @package Aheadworks\Sarp2\Block\Product
 */
class SubscriptionOptions extends Template
{
    /**
     * @var IsSubscription
     */
    private $isSubscriptionChecker;

    /**
     * @var SubscriptionOptionSource
     */
    private $optionSource;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * todo: consider move all 'configs' into \Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface
     *       or another data interface. This will make available it on Web API layer
     * @var ConfigProviderPool
     */
    private $configProviderPool;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var array
     */
    private $optionsArray;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'product/subscription_options.phtml';

    /**
     * @param Context $context
     * @param IsSubscription $isSubscriptionChecker
     * @param SubscriptionOptionSource $optionSource
     * @param ProductRepositoryInterface $productRepository
     * @param ConfigProviderPool $configProviderPool
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        IsSubscription $isSubscriptionChecker,
        SubscriptionOptionSource $optionSource,
        ProductRepositoryInterface $productRepository,
        ConfigProviderPool $configProviderPool,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->isSubscriptionChecker = $isSubscriptionChecker;
        $this->optionSource = $optionSource;
        $this->productRepository = $productRepository;
        $this->configProviderPool = $configProviderPool;
        $this->registry = $registry;
    }

    /**
     * Get subscription option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        if ($this->optionsArray === null) {
            $productId = $this->getProductId();
            $this->optionsArray = $this->optionSource->getOptionArray($productId);
        }

        return $this->optionsArray;
    }

    /**
     * Get default option Id
     *
     * @return int
     */
    public function getDefaultOptionId()
    {
        if ($this->isSubscriptionChecker->checkById($this->getProductId(), true)) {
            $optionArray = $this->getOptionArray();
            $optionIds = array_keys($optionArray);
            return $optionIds[0];
        }
        return 0;
    }

    /**
     * Get config data
     *
     * @return array
     */
    public function getConfigData()
    {
        $productTypeId = $this->getProduct()->getTypeId();
        $configData = $this->configProviderPool->getConfigProvider($productTypeId)
            ->getConfig($this->getProductId(), $productTypeId);

        return \Zend_Json::encode($configData);
    }

    /**
     * Get product Id
     *
     * @return int|null
     */
    private function getProductId()
    {
        return $this->getProduct()->getId();
    }

    /**
     * Get product
     *
     * @return ProductInterface|Product
     */
    private function getProduct()
    {
        return $this->registry->registry('product');
    }

    /**
     * {@inheritdoc}
     */
    public function toHtml()
    {
        if ($this->isSubscriptionChecker->checkById($this->getProductId())) {
            return parent::toHtml();
        }
        return '';
    }

    /**
     * Check if product has options
     *
     * @return bool
     */
    public function hasOptions()
    {
        /** @var ProductInterface $product */
        try {
            $product = $this->getProduct();
            $options = $product->getOptions();
            return $options && count($options) > 0;
        } catch (NoSuchEntityException $e) {
        }

        return false;
    }
}
