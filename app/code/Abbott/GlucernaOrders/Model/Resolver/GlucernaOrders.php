<?php

namespace Abbott\GlucernaOrders\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Framework\Exception\LocalizedException;

class GlucernaOrders implements ResolverInterface
{

    const SKU = 'sku';
    const NAME = 'name';
    const EMAIL = 'customer_email';
    const FLAVORS = 'flavors';
    const QTY = 'qty';
    const TRIAL_ELIGIBLE = 'trial_eligible';
    const YES = 'yes';
    const NO = 'no';
    const IS_TRIAL = 'is_trial';
    const PLAN = 'plan';
    const SUB_TYPE = 'aw_sarp2_subscription_type';
    const BACKORDERS = 'backorders';

    /**
     *
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     *
     * @var \Magento\Framework\Pricing\PriceInfo\Factory
     */
    private $priceInfoFactory;

    /**
     *
     * @var \Aheadworks\Sarp2\Api\PlanRepositoryInterface
     */
    private $planRepository;

    /**
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     *
     * @var \Abbott\GlucernaOrders\Model\ResourceModel\Managesubscription\CollectionFactory
     */
    protected $subCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Abbott\GlucernaOrders\Helper\Data
     */
    protected $glucernaHelper;

     /**
      * @var \Abbott\Backorder\Helper\Data
      */
    protected $backorderHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $context;

    /**
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Pricing\PriceInfo\Factory $priceInfoFactory
     * @param \Aheadworks\Sarp2\Api\PlanRepositoryInterface $planRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Abbott\GlucernaOrders\Model\ResourceModel\Managesubscription\CollectionFactory $subCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Abbott\GlucernaOrders\Helper\Data $glucernaHelper
     * @param \Abbott\Backorder\Helper\Data $backorderHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory
     * @param \Magento\Authorization\Model\UserContextInterface $context
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Pricing\PriceInfo\Factory $priceInfoFactory,
        \Aheadworks\Sarp2\Api\PlanRepositoryInterface $planRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Abbott\GlucernaOrders\Model\ResourceModel\Managesubscription\CollectionFactory $subCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Abbott\GlucernaOrders\Helper\Data $glucernaHelper,
        \Abbott\Backorder\Helper\Data $backorderHelper,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Magento\Authorization\Model\UserContextInterface $context
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->productFactory = $productFactory;
        $this->priceInfoFactory = $priceInfoFactory;
        $this->planRepository = $planRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
        $this->subCollectionFactory = $subCollectionFactory;
        $this->glucernaHelper = $glucernaHelper;
        $this->backorderHelper = $backorderHelper;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->context = $context;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @inheritdoc
     *
     * Format product's price, tier price and stock data to conform to GraphQL schema
     *
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @throws \Exception
     * @return array
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            $flavors = [];
            $configSku = $args[self::SKU];
            $basicSku = [];
            $skuProduct = $this->productFactory->create();
            $skuProduct->getResource()->load($skuProduct, $skuProduct->getIdBySku($configSku));
            $children = $skuProduct->getTypeInstance()->getUsedProducts($skuProduct);
            foreach ($children as $child) {
                $basicSku[] = $child->getSku();
            }
            $flavorsArr = [[ ]];
            foreach ($basicSku as $element) {
                $flavor = [];
                $eleProduct = $this->productFactory->create();
                $eleProduct->getResource()->load($eleProduct, $eleProduct->getIdBySku($element));
                $flavor['flavor'] = $eleProduct->getAttributeText(self::FLAVORS);
                $isBackorder = $this->backorderHelper->getBackorderStatus($eleProduct);
                if ($isBackorder) {
                    $flavor['backorder_message'] = $this->glucernaHelper->getConfig();
                }
                $flavors[] = $flavor;
                $flavorsArr = $this->getFlavorsCombo($flavorsArr, $element);
            }
            $result = $this->getCombinations($flavorsArr, $flavors);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $result;
    }

    public function permute($array)
    {
        if (count($array) === 1) {
            return $array;
        }
        $result = [];
        foreach ($array as $key => $item) {
            foreach ($this->permute(array_diff_key($array, [$key => $item])) as $p) {
                $result[] = $item .','. $p;
            }
        }
        return $result;
    }

    public function getFlavorsCombo($flavorsArr, $element)
    {
        $flapArr = $flavorsArr;
        foreach ($flavorsArr as $flavorsComb) {
            if (empty($flavorsComb)) {
                array_push($flapArr, [$element]);
            } else {
                array_push($flavorsComb, $element);
                array_push($flapArr, $flavorsComb);
            }
        }
        return $flapArr;
    }

    public function getCombinations($flavorsArr, $flavors)
    {
        $result = [];
        $finalresult[self::TRIAL_ELIGIBLE] = self::YES;
        if (count($flavorsArr)) {
            $orders = $this->getOrderHistory();
            $result = $this->getPlanCombos($flavorsArr, $orders);
            if (!is_array($orders) && $orders->getSize()) {
                $finalresult[self::TRIAL_ELIGIBLE] = self::NO;
            }
        } else {
            throw new LocalizedException(new \Magento\Framework\Phrase(
                'The sku given does not have any child products'
            ));
        }
        if (count($flavors)) {
            $finalresult[self::FLAVORS] = $flavors;
        } else {
            throw new LocalizedException(new \Magento\Framework\Phrase(
                'The child products of the given sku do not have any assigned flavors'
            ));
        }
        $finalresult['combinations'] = $result;
        return $finalresult;
    }

    public function getPlanCombos($flavorsArr, $orders)
    {
        $result = [];
        foreach ($flavorsArr as $flavorComb) {
            if (count($flavorComb) > 0) {
                $flavorperm = $this->permute($flavorComb);
                $productCollection = $this->productCollectionFactory->create();
                $productCollection->addAttributeToSelect('*')
                    ->addAttributeToFilter('status', '1')
                    ->addAttributeToFilter('group_sku', ['in' => $flavorperm])
                    ->addAttributeToFilter('glucerna_funnel_index', 1)
                    ->addStoreFilter($this->storeManager->getStore()->getId());

                if (!is_array($orders) && $orders->getSize()) {
                    $productCollection->addAttributeToFilter('allow_trial', '0');
                }

                $combination = [];
                $productflavor = [];
                foreach ($flavorComb as $flavor) {
                    $productflavor[] = $this->getFlavorArray($flavor);
                }
                $combflavor = implode(",", $productflavor);
                $plans = $this->getPlans($productCollection, $productflavor);
                $combination['combination'] = $combflavor;
                $combination['plans'] = $plans;
                $result[] = $combination;
            }
        }
        return $result;
    }

    public function getOrderHistory()
    {
        $orders = [];
        $customerId = $this->context->getUserId();
        if ($customerId) {
            $orders = $this->orderCollectionFactory->create()
                           ->addFieldToFilter('customer_id', $customerId);
        }
        return $orders;
    }

    public function getPlans($productCollection, $productflavor)
    {
        if ($productCollection->getSize()) {
            return $this->getPlanDetails($productCollection, $productflavor);
        } else {
            throw new LocalizedException(new \Magento\Framework\Phrase(
                'The combination products do not have funnel index set to 1 or have no group skus assigned to them'
            ));
        }
    }

    public function getPlanDetails($productCollection, $productflavor)
    {
        $plans = [];
        $subscriptionType = [2,3];
        foreach ($productCollection as $product) {
            $planDetails = [];
            $planDetails[self::PLAN] = $product->getAttributeText('glucerna_product_plan');
            $subCollection = $this->subCollectionFactory->create()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('plan_name', $planDetails[self::PLAN]);
            if ($subCollection->getSize()) {
                $rest = $subCollection->getFirstItem();
                $planDetails[self::IS_TRIAL] = self::NO;
                if ($rest->getIsTrialPlan()) {
                    $planDetails[self::IS_TRIAL] = self::YES;
                    $planDetails['subscription_msg'] = $rest->getPlanLabel();
                    $planDetails['subscription_sku'] = $product->getData('actual_trial_sku_mapping');
                    $priceDetails = explode(",", $rest->getPlanPrice());
                    $planDetails['original_price'] = $priceDetails[0];
                    $planDetails['trial_price'] = $priceDetails[1];
                    $planDetails['trial_period'] = $rest->getTrialPeriod();
                }
                $planDetails['shipping_price'] = $rest->getPlanShippingRate();
                $planDetails['plan_qty'] = $rest->getPlanValue();
            }
            $delSpl = explode(",", $product->getData('glucerna_delivery_split'));
            $deliverySplit = $this->getDeliverySplit($delSpl, $productflavor);
            $planDetails['delivery_split'] = $deliverySplit;
            $planDetails['product_id'] = $product->getData('entity_id');
            $planDetails[self::SKU] = $product->getData(self::SKU);
            $planDetails[self::NAME] = $product->getData(self::NAME);
            $optionProduct = $this->productFactory->create();
            $optionProduct->getResource()->load($optionProduct, $optionProduct->getIdBySku($planDetails[self::SKU]));
            if (in_array($optionProduct->getData(self::SUB_TYPE), $subscriptionType)) {
                $options = $optionProduct->getData('aw_sarp2_subscription_options');
                $planDetails[self::SUB_TYPE] = $this->getSubscriptionOption($options, $planDetails[self::SKU]);
            }
            $priceInfo = $this->priceInfoFactory->create($product);
            $regularPriceAmount =  $priceInfo->getPrice(RegularPrice::PRICE_CODE)->getAmount();
            $planDetails['price'] = $regularPriceAmount->getValue();
            $stockDetails = $this->stockRegistry->getStockItemBySku($planDetails[self::SKU]);
            $planDetails[self::BACKORDERS] = (
                $stockDetails[self::QTY] < 1 && $stockDetails[self::BACKORDERS] == 2
            ) ? self::YES : self::NO;
            $planDetails[self::QTY] = $stockDetails[self::QTY];
            $plans[] = $planDetails;
        }
        return $plans;
    }

    public function getSubscriptionOption($options, $sku)
    {
        $count = count($options);
        if (!$count) {
            throw new LocalizedException(new \Magento\Framework\Phrase(
                'There are no subscription options for the sku ' . $sku
            ));
        }
        if ($count == 1) {
            return $options[0]['option_id'];
        }
        foreach ($options as $option) {
            if ($option['trial_price'] > 0) {
                return $option['option_id'];
            }
        }
    }

    public function getFlavorArray($flavor)
    {
        $flavorpro = $this->productFactory->create();
        $flavorpro->getResource()->load($flavorpro, $flavorpro->getIdBySku($flavor));
        return $flavorpro->getAttributeText(self::FLAVORS);
    }

    public function getDeliverySplit($delSpl, $productflavor)
    {
        $deliverySplit = [];
        foreach ($delSpl as $key => $split) {
            $delSplit = [];
            $delSplit['split_qty'] = $split;
            $delSplit['split_flavor'] = $productflavor[$key];
            $deliverySplit[] = $delSplit;
        }
        return $deliverySplit;
    }
}
