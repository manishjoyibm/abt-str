<?php

namespace Abbott\Sarp2\Block\Product;

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
 * @package Abbott\Sarp2\Block\Product
 */
class SubscriptionOptions extends \Aheadworks\Sarp2\Block\Product\SubscriptionOptions
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
        $this->isSubscriptionChecker = $isSubscriptionChecker;
        $this->optionSource = $optionSource;
        $this->productRepository = $productRepository;
        $this->configProviderPool = $configProviderPool;
        $this->registry = $registry;
        parent::__construct(
            $context,
            $isSubscriptionChecker, 
            $optionSource,
            $productRepository,
            $configProviderPool,
            $registry,
            $data
        );
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

        return \Laminas\Json\Json::encode($configData);
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
}
