<?php

namespace Abbott\ProgressiveDiscount\Model\Profile\Item;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Engine\PaymentFactory;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangePlan\Applier;
use Aheadworks\Sarp2\Model\Sales\CopySelf;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderItemInterfaceFactory;
use Magento\Sales\Model\Order\Item;
use Magento\Tax\Model\Config as TaxConfig;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Abbott\MyAccount\Helper\Data as AccountHelper;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ToOrderItem extends \Aheadworks\Sarp2\Model\Profile\Item\ToOrderItem
{
    public $objectManager;
    /**
     * @var \Aheadworks\Sarp2\Engine\PaymentFactory
     */
    public $paymentFactory;
    public $resource;
    public $profileRepository;
    /**
     * @var OrderItemInterfaceFactory
     */
    private $orderItemFactory;

    public const PRODUCTID='product_id';
    public const TRIAL='allow_trial';
    public const ACTSKUMAP='actual_trial_sku_mapping';

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @var CopySelf
     */
    private $selfCopyService;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var TaxConfig
     */
    private $taxConfig;

    protected $applier;

    protected $planRepository;

    protected $optionsRepository;

    protected $profileManagement;

    protected $product;

    protected $productRepository;

    /**
     * @var array
     */
    private $selfCopyMapExcludeTax = [
        [OrderItemInterface::PRICE, OrderItemInterface::ORIGINAL_PRICE],
        [OrderItemInterface::BASE_PRICE, OrderItemInterface::BASE_ORIGINAL_PRICE],
        [OrderItemInterface::BASE_PRICE, OrderItemInterface::BASE_COST],
    ];

    /**
     * @var array
     */
    private $selfCopyMapIncludeTax = [
        [OrderItemInterface::PRICE_INCL_TAX, OrderItemInterface::ORIGINAL_PRICE],
        [OrderItemInterface::BASE_PRICE_INCL_TAX, OrderItemInterface::BASE_ORIGINAL_PRICE],
        [OrderItemInterface::BASE_PRICE_INCL_TAX, OrderItemInterface::BASE_COST],
    ];

    /**
     * Constructor
     *
     * @param OrderItemInterfaceFactory $orderItemFactory
     * @param Copy $objectCopyService
     * @param CopySelf $selfCopyService
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param TaxConfig $taxConfig
     * @param Applier $applier
     * @param PlanRepositoryInterface $planRepository
     * @param SubscriptionOptionRepositoryInterface $optionsRepository
     * @param ObjectManagerInterface $objectManager
     * @param PaymentFactory $paymentFactory
     * @param ResourceConnection $resource
     * @param ProfileRepositoryInterface $profileRepository
     * @param Product $product
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        OrderItemInterfaceFactory $orderItemFactory,
        Copy $objectCopyService,
        CopySelf $selfCopyService,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        TaxConfig $taxConfig,
        Applier $applier,
        PlanRepositoryInterface $planRepository,
        SubscriptionOptionRepositoryInterface $optionsRepository,
        ObjectManagerInterface $objectManager,
        PaymentFactory $paymentFactory,
        ResourceConnection $resource,
        ProfileRepositoryInterface $profileRepository,
        Product $product,
        ProductRepositoryInterface $productRepository
    ) {
        $this->orderItemFactory = $orderItemFactory;
        $this->objectCopyService = $objectCopyService;
        $this->selfCopyService = $selfCopyService;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->taxConfig = $taxConfig;
        $this->applier = $applier;
        $this->planRepository = $planRepository;
        $this->optionsRepository = $optionsRepository;
        $this->objectManager = $objectManager;
        $this->paymentFactory = $paymentFactory;
        $this->resource = $resource;
        $this->profileRepository = $profileRepository;
        $this->product = $product;
        $this->productRepository = $productRepository;
    }

    /**
     * Convert profile item to order item
     *
     * @param ProfileItemInterface $profileItem
     * @param $paymentPeriod
     * @param $data
     * @return OrderItemInterface|Item
     * @throws LocalizedException
     */
    public function convert(ProfileItemInterface $profileItem, $paymentPeriod, $data = [])
    {
        $profileItemClone = clone $profileItem;
        $options = $profileItemClone->getProductOptions();
        $profileData = $this->profileRepository->get($profileItemClone->getProfileId());
        $this->dataObjectHelper->populateWithArray(
            $profileItemClone,
            $this->dataObjectProcessor->buildOutputDataArray($profileItemClone, ProfileItemInterface::class),
            ProfileItemInterface::class
        );
        $orderItemData = $this->objectCopyService->getDataFromFieldset(
            'aw_sarp2_convert_profile_item',
            'to_order_item',
            $profileItemClone
        );
        $orderItemData = array_merge(
            $orderItemData,
            $this->objectCopyService->getDataFromFieldset(
                'aw_sarp2_convert_profile_item',
                'to_order_item_' . $paymentPeriod,
                $profileItemClone
            )
        );
        $storeId = $profileItemClone->getStoreId();
        $isPriceIncludesTax = $this->taxConfig->priceIncludesTax($storeId);
        $orderItemData = $isPriceIncludesTax
        ? $this->selfCopyService->copyByMap($orderItemData, $this->selfCopyMapIncludeTax)
        : $this->selfCopyService->copyByMap($orderItemData, $this->selfCopyMapExcludeTax);
        if (!empty($data)) {
            $orderItemData = array_merge($orderItemData, $data);
        }
        if ($storeId == AccountHelper::GLU_STORE_ID) {
            $nextSkuData = $this->getGlucernaOrderItemData($orderItemData);
            if ($nextSkuData) {
                $planDetails = $this->getPlanDetailsForProduct($orderItemData);
                $newPlanProd = $planDetails->getData('aw_sarp2_subscription_options');
                $newPlanId = $newPlanProd[0]['plan_id'];
                $newPlan = $this->planRepository->get($newPlanId);
                $newOption = $this->getOptionByPlan($planDetails->getId(), $newPlanId);
                $newPlanArray = $this->dataObjectProcessor->buildOutputDataArray(
                    $newPlan,
                    PlanInterface::class
                );
                $newOptionArray = $this->dataObjectProcessor->buildOutputDataArray(
                    $newOption,
                    SubscriptionOptionInterface::class
                );
                unset($newOptionArray[SubscriptionOptionInterface::PLAN]);
                unset($newOptionArray[SubscriptionOptionInterface::PRODUCT]);
                $options['aw_sarp2_subscription_option'] = $newOptionArray;
                $options['aw_sarp2_subscription_plan'] = $newPlanArray;
                $orderItemData = $nextSkuData;
                $this->objectManager->create('\Aheadworks\Sarp2\Api\ProfileManagementInterface')
                ->changeSubscriptionPlan($profileItemClone->getProfileId(), $newPlanId);
                $this->updateProfileItemTable($profileItemClone->getProfileId(), $nextSkuData);
                $this->updateScheduleFrequency($profileData, $profileItemClone->getProfileId());
            }
        }
        //checking updated qty from profile table
        if ($profileData) {
            $orderItemData['qty_ordered'] =  $profileItemClone->getQty();
        }
        /** @var OrderItemInterface|Item $orderItem */
        $orderItem = $this->orderItemFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $orderItem,
            $orderItemData,
            OrderItemInterface::class
        );
        $orderItem
            ->setProductOptions($options)
            ->setDiscountAmount(0.0);
        if ($paymentPeriod == PaymentInterface::PERIOD_INITIAL) {
            $orderItem->setIsVirtual(true);
        }
        return $orderItem;
    }

    /**
     * UpdateProfileItemTable
     *
     * @param $profieId
     * @param $productData
     * @return void
     */
    public function updateProfileItemTable($profieId = null, $productData = [])
    {
        if ($profieId) {
            $connection = $this->resource->getConnection();
            $table = $connection->getTableName('aw_sarp2_profile_item');
            if (isset($productData[self::PRODUCTID])) {
                $connection->update(
                    $table,
                    [
                        self::PRODUCTID => $productData[self::PRODUCTID],
                        'sku' => $productData['sku'],
                        'name' => $productData['name']
                    ],
                    ['profile_id IN(?)' => $profieId]
                );
            }
        }
    }

    /**
     * GetOptionByPlan
     *
     * @param $productId
     * @param $newPlanId
     * @return SubscriptionOptionInterface|null
     * @throws LocalizedException
     */
    private function getOptionByPlan($productId, $newPlanId)
    {
        $subscriptionOptions = $this->optionsRepository->getList($productId);
        /** @var SubscriptionOptionInterface $option */
        foreach ($subscriptionOptions as $option) {
            if ($newPlanId == $option->getPlanId()) {
                return $option;
            }
        }
        return null;
    }

    /**
     * GetGlucernaOrderItemData
     *
     * @param $product
     * @return array|false
     * @throws NoSuchEntityException
     */
    public function getGlucernaOrderItemData($product = [])
    {
        $productArray = [];
        if (isset($product[self::PRODUCTID])) {
            $productId = $product[self::PRODUCTID];
            $currProduct = $this->product->load($productId);
            if (!$currProduct->getData(self::TRIAL)) {
                return false;
            }
            $nextSku = $currProduct->getData(self::ACTSKUMAP);
            $product = $this->productRepository->get($nextSku);
            if ($product) {
                $productArray['store_id'] = 2;
                $productArray[self::PRODUCTID] = $product->getId();
                $productArray['product_type'] = $product->getTypeId();
                $productArray['sku'] = $product->getSku();
                $productArray['name'] = $product->getName();
                $productArray['description'] = $product->getDescription();
                $productArray['weight'] = $product->getWeight();
                $productArray['row_weight'] = $product->getWeight();
                $productArray['is_qty_decimal'] = null;
                $productArray['qty_ordered'] = 1;
                $productArray['is_virtual'] = null;
                $productArray['base_price'] = $product->getPrice();
                $productArray['price'] = $product->getPrice();
                $productArray['base_tax_amount'] = 0;
                $productArray['tax_amount'] = 0;
                $productArray['tax_percent'] = 0;
                $productArray['base_row_total'] = $product->getPrice();
                $productArray['row_total'] = $product->getPrice();
                $productArray['base_price_incl_tax'] = $product->getPrice();
                $productArray['price_incl_tax'] = $product->getPrice();
                $productArray['base_row_total_incl_tax'] = $product->getPrice();
                $productArray['row_total_incl_tax'] = $product->getPrice();
                $productArray['original_price'] = $product->getPrice();
                $productArray['base_original_price'] = $product->getPrice();
                $productArray[self::ACTSKUMAP] = $product->getData(self::ACTSKUMAP);
                $productArray[self::TRIAL] = $product->getData(self::TRIAL);
            }
        }
        return $productArray;
    }

    /**
     * GetPlanDetailsForProduct
     *
     * @param $product
     * @return ProductInterface|void
     * @throws NoSuchEntityException
     */
    public function getPlanDetailsForProduct($product = [])
    {
        if (isset($product[self::PRODUCTID])) {
            $productId = $product[self::PRODUCTID];
            $currProduct = $this->product->load($productId);
            $nextSku = $currProduct->getData(self::ACTSKUMAP);
            return $this->productRepository->get($nextSku);
        }
    }

    /**
     * UpdateScheduleFrequency
     *
     * @param $profileData
     * @param $profileId
     * @return void
     */
    public function updateScheduleFrequency($profileData, $profileId)
    {
        $connection = $this->resource->getConnection();
        $profileDefTable = $connection->getTableName('aw_sarp2_profile_definition');
        $planDefTable = $connection->getTableName('aw_sarp2_plan_definition');
        $planDefSelect = $connection->select()
                ->from($planDefTable)
                ->where('definition_id = ?', $profileData->getPlanDefinitionId());
        $data = $connection->fetchRow($planDefSelect);
        $days = $data['billing_frequency'];
        $connection->update(
            $profileDefTable,
            ["billing_frequency" => $days],
            ['definition_id IN(?)' => $profileData->getProfileDefinitionId()]
        );
    }
}
