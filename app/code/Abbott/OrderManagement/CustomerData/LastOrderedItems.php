<?php

namespace Abbott\OrderManagement\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\State;
use Abbott\MyAccount\Helper\Data as MyAccountData;

/**
 * Returns information for "Recently Ordered" widget.
 */
class LastOrderedItems extends \Magento\Sales\CustomerData\LastOrderedItems
{
    public $redirectHelper;
    /**
     * Limit of orders in side bar
     */
    const SIDEBAR_ORDER_LIMIT = 5;

    const FLAVORS = 'flavors';

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderInterface
     */
    private $lastorder;

    /**
     * @var Attribute
     */
    private $eavModel;

    /**
     * @var State
     */
    protected $state;

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     * @param OrderInterface $lastorder
     * @param Attribute $eavModel
     * @param State $state
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        \Abbott\CustomerTransistion\Helper\Data $redirectHelper,
        LoggerInterface $logger,
        OrderInterface $lastorder,
        Attribute $eavModel,
        State $state
    ) {
        $this->lastorder = $lastorder;
        $this->eavModel = $eavModel;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->redirectHelper = $redirectHelper;
        $this->logger = $logger;

        parent::__construct(
            $orderCollectionFactory,
            $orderConfig,
            $customerSession,
            $stockRegistry,
            $storeManager,
            $productRepository,
            $logger
        );

        $this->state = $state;
    }

    /**
     * Get list of last ordered products
     *
     * @return array
     */
    protected function getItems(): array
    {
        $items = [];

        if ($this->state->getAreaCode() == 'frontend' &&
            $this->storeManager->getStore()->getCode() == MyAccountData::NEW_SIM_STORE_CODE) {
            return $items;
        }

        $order = $this->getLastOrder();

        if ($order) {
            $orderItems = $order->getAllVisibleItems();
            $website = $this->storeManager->getStore()->getWebsiteId();
            /** @var \Magento\Sales\Model\Order\Item $item */
            foreach ($orderItems as $oItems) {
                $optionText = '';
                /** @var \Magento\Catalog\Model\Product $product */
                try {
                    $product = $this->productRepository->getById(
                        $oItems->getProductId(),
                        false,
                        $this->storeManager->getStore()->getId()
                    );

                    $options = $oItems->getProductOptions();
                    if (!empty($options['info_buyRequest']) && $product->getAttributeText(self::FLAVORS)) {
                        $optionText = $product->getAttributeText(self::FLAVORS);
                    }
                } catch (NoSuchEntityException $noEntityException) {
                    $this->logger->critical($noEntityException);
                    continue;
                }
                if (isset($product) && in_array($website, $product->getWebsiteIds())) {
                    $aemUrl = rtrim($this->redirectHelper->getFailureUrl(), '/').''.$product->getData('aem_url');
                    $items[] = [
                        'id' => $oItems->getId(),
                        'name' => $oItems->getName(),
                        self::FLAVORS => $optionText,
                        'url' => $aemUrl,
                        'is_saleable' => $this->isItemAvailableForReorder($oItems),
                    ];
                }
            }
        }

        return $items;
    }
}
