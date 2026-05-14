<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Processor;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Source\Frontend as FrontendSubscriptionOptionSource;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Block\Product\ImageBuilder as ProductImageBuilder;
use Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\ProviderPool as ConfigProviderPool;

/**
 * Class Plan
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit
 */
class Plan extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductUrl
     */
    private $productUrl;

    /**
     * @var CurrencyFactory
     */
    private $currencyFactory;

    /**
     * @var ProductImageBuilder
     */
    private $productImageBuilder;

    /**
     * @var FrontendSubscriptionOptionSource
     */
    private $subscriptionOptionSource;

    /**
     * todo: consider move all 'configs' into \Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface
     *       or another data interface. This will make available it on Web API layer
     * @var ConfigProviderPool
     */
    private $configProviderPool;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ProductRepositoryInterface $productRepository
     * @param ProductUrl $productUrl
     * @param CurrencyFactory $currencyFactory
     * @param ProductImageBuilder $productImageBuilder
     * @param FrontendSubscriptionOptionSource $subscriptionOptionSource
     * @param ConfigProviderPool $configProviderPool
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        ProductUrl $productUrl,
        CurrencyFactory $currencyFactory,
        ProductImageBuilder $productImageBuilder,
        FrontendSubscriptionOptionSource $subscriptionOptionSource,
        ConfigProviderPool $configProviderPool,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->productUrl = $productUrl;
        $this->currencyFactory = $currencyFactory;
        $this->productImageBuilder = $productImageBuilder;
        $this->subscriptionOptionSource = $subscriptionOptionSource;
        $this->configProviderPool = $configProviderPool;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Retrieve profile
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->registry->registry('profile');
    }

    /**
     * Retrieve product
     *
     * @param int $productId
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getProduct($productId)
    {
        return $this->productRepository->getById($productId);
    }

    /**
     * Retrieve save url
     *
     * @param int $profileId
     * @return string
     */
    public function getSaveUrl($profileId)
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp2/profile_edit/savePlan',
            ['profile_id' => $profileId]
        );
    }

    /**
     * Check if product exists
     *
     * @param int $productId
     * @return bool
     */
    public function isProductExists($productId)
    {
        try {
            $this->getProduct($productId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
        return true;
    }

    /**
     * Retrieve product image html
     *
     * @param int $productId
     * @return string
     */
    public function getProductImageHtml($productId)
    {
        /** @var ProductInterface|Product $product */
        $product = $this->productRepository->getById($productId);
        return $this->productImageBuilder->setProduct($product)
            ->setImageId('product_base_image')
            ->create()
            ->toHtml();
    }

    /**
     * Check if product has url
     *
     * @param int $productId
     * @return bool
     */
    public function hasProductUrl($productId)
    {
        /** @var ProductInterface|Product $product */
        $product = $this->productRepository->getById($productId);
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get product url
     *
     * @param int $productId
     * @return string
     */
    public function getProductUrl($productId)
    {
        /** @var ProductInterface|Product $product */
        $product = $this->productRepository->getById($productId);
        return $this->productUrl->getUrl($product);
    }

    /**
     * Retrieve default option id
     *
     * @return int|null
     */
    public function getDefaultOptionId()
    {
        return $this->getProfile()->getPlanId();
    }

    /**
     * Get subscription option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $intersectOptionArray = [];
        foreach ($this->getProfile()->getItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $options = $this->subscriptionOptionSource->getPlanOptionArray($item->getProductId());
            $intersectOptionArray = $intersectOptionArray
                ? array_intersect_key($options, $intersectOptionArray)
                : $options;
        }

        return $intersectOptionArray;
    }

    /**
     * Get config data
     *
     * @return array
     */
    public function getConfigData()
    {
        $newDetails = [];
        foreach ($this->getProfile()->getItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $productId = $item->getProductId();
            $product = $this->getProduct($productId);
            $productTypeId = $product->getTypeId();
            $productSubscriptionDetails = $this->configProviderPool
                ->getConfigProvider($productTypeId)
                ->getSubscriptionDetailsConfig($productId, $item, $this->getProfile());

            foreach ($productSubscriptionDetails as $planId => $details) {
                $trial = $this->getSubscriptionDetailsByType($details, Processor::TRIAL_PAYMENT);
                if (!isset($newDetails[$planId])) {
                    if ($this->getDefaultOptionId() == $planId && $trial) {
                        $newDetails[$planId][] = $trial;
                    }

                    $newDetails[$planId][] = $this->getSubscriptionDetailsByType($details, Processor::REGULAR_PAYMENT);
                    $newDetails[$planId][] = $this->getSubscriptionDetailsByType($details, Processor::BILLING_CYCLE);
                } else {
                    foreach ($newDetails[$planId] as &$newDetail) {
                        if ($newDetail['type'] == Processor::REGULAR_PAYMENT) {
                            $productDetail = $this->getSubscriptionDetailsByType($details, $newDetail['type']);
                            $newDetail['composite_value']['value'] += $productDetail['composite_value']['value'];

                            $billingCycles = $newDetail['composite_value']['billing_cycles'];
                            $price = $this->priceCurrency->format($newDetail['composite_value']['value'], false);
                            $newDetail['value'] = $billingCycles > 0
                                ? __('%1 x %2', $billingCycles, $price)
                                : __('%2', $price);
                        }
                    }
                }
            }
        }

        $configData = [
            'regularPrices' => ['options' => []],
            'subscriptionDetails' => $newDetails,
            'productType' => Product\Type::TYPE_SIMPLE,
            'productId' => 1
        ];

        return \Zend_Json::encode($configData);
    }

    /**
     * Retrieve subscription details by type
     *
     * @param array $details
     * @param string $type
     * @return null
     */
    private function getSubscriptionDetailsByType($details, $type)
    {
        foreach ($details as $detail) {
            if ($detail['type'] == $type) {
                return $detail;
            }
        }
        return null;
    }
}
