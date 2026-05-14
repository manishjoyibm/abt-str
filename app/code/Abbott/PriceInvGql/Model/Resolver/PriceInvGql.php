<?php

declare(strict_types=1);

namespace Abbott\PriceInvGql\Model\Resolver;

use Abbott\Strongmoms\Helper\Data;
use Abbott\WorkdayFeed\Helper\InboundFeedHelper;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\OptionRepository;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Catalog\Model\Product\TierPrice;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory as ProfilecollectionFactory;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\Pricing\PriceInfo\Factory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Abbott\ProgressiveDiscount\Model\ResourceModel\ManageDiscountCodes\CollectionFactory as DiscountFactory;
use Abbott\MetabolicOrdering\Model\MetabolicFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Abbott\MetabolicOrdering\Helper\Data as MetabolicData;
use Magento\Store\Model\StoreManagerInterface;

class PriceInvGql implements ResolverInterface
{

    public $optionRepository;
    public $productResourceFactory;
    public $categoryCollectionFactory;
    public $custGroupRepository;
    public $scopeConfig;
    public $ssmHelper;
    public $checkoutHelper;
    public const NLOG = "NOT LOGGED IN";
    public const SPECIAL_PRICE = "special_price";
    public const GROUP_PRICE = "group_price";
    public const ITEM_ID = "item_id";
    public const PRODUCT_ID = "product_id";
    public const STOCK_ID = "stock_id";
    public const IS_IN_STOCK = "is_in_stock";
    public const BACKORERS = "backorders";
    public const PRICE = "price";
    public const SUBSCRIPTION_TYPE = "aw_sarp2_subscription_type";
    public const PLAN_ID = "plan_id";
    public const OPTION_ID = "option_id";
    public const NAME = "name";
    public const PERCENT = "percent";
    public const SUBSCRIBE_GRP = "subscribe_customer_group";
    public const CUSTOMER_GRP = "custGrp";
    public const IS_PROGRESSIVE = "is_progressive";
    public const CUSTOMER_ID = "customer_id";
    public const GROUP_MESSAGE = "group_message";
    public const PROGRESSIVE_PLAN_ERROR_MESSAGE =
        "aboott_message/progressive_plan_error_message/message_for_product_detail";
    public const SSM_PROGRESSIVE_PLAN_ERROR_MESSAGE =
        "aboott_message/ssm_progressive_plan_error_message/ssm_message_for_prg_product_detail";
    public const SSM_TEN_PERCENT_PLAN_ERROR_MESSAGE =
        "aboott_message/ssm_progressive_plan_error_message/ssm_message_for_product_detail";
    public const NON_SSM_PROGRESSIVE_PLAN_ERROR_MESSAGE  =
        "aboott_message/ssm_progressive_plan_error_message/non_ssm_message_for_prg_product_detail";
    public const GUEST_PROGRESSIVE_PLAN_ERROR_MESSAGE  =
        "aboott_message/ssm_progressive_plan_error_message/guest_message_for_prg_product_detail";
    public const NON_SSM_TEN_PERCENT_PLAN_ERROR_MESSAGE =
        "aboott_message/ssm_progressive_plan_error_message/non_ssm_message_for_product_detail";
    public const GUEST_TEN_PERCENT_PLAN_ERROR_MESSAGE =
        "aboott_message/ssm_progressive_plan_error_message/guest_message_for_product_detail";
    public const BRAND = 'Metabolics';
    public const AVAILABLE_FOR_CALL = 1;
    public const LEVEL = 'Level1';

    /**
     *
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     *
     * @var ProductFactory
     */
    private $productFactory;

    /**
     *
     * @var Factory
     */
    private $priceInfoFactory;

    private $context;

    /**
     *
     * @var PlanRepositoryInterface
     */
    private $planRepository;
    /**
     * @var CollectionFactory
     */
    private $profileCollectionFactory;

    public $storeManager;

    /**
     * DiscountFactory
     */
    protected $dcr;

    /**
     * @var InboundFeedHelper
     */
    protected $workdayHelper;

    /**
     * @var \Abbott\PriceInvGql\Helper\Data
     */
    protected $priceInvHelper;

    protected $metabolicFactory;

    protected $customerSession;

    protected $timezoneInterface;

    protected $metabolicData;

    /**
     * Construct
     *
     * @param StockRegistryInterface $stockRegistry
     * @param ProductFactory $productFactory
     * @param Factory $priceInfoFactory
     * @param PlanRepositoryInterface $planRepository
     * @param OptionRepository $optionRepository
     * @param \Magento\Catalog\Model\ResourceModel\ProductFactory $productResourceFactory
     * @param CollectionFactory $categoryCollectionFactory
     * @param GroupRepositoryInterface $custGroupRepository
     * @param ProfilecollectionFactory $profileCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param DiscountFactory $dcr
     * @param Data $ssmHelper
     * @param \Abbott\Checkout\Helper\Data $checkoutHelper
     * @param InboundFeedHelper $workdayHelper
     * @param \Abbott\PriceInvGql\Helper\Data $priceInvHelper
     * @param MetabolicFactory $metabolicFactory
     * @param CustomerSession $customerSession
     * @param TimezoneInterface $timezoneInterface
     * @param MetabolicData $metabolicData
     */
    public function __construct(
        StockRegistryInterface $stockRegistry,
        ProductFactory $productFactory,
        Factory $priceInfoFactory,
        PlanRepositoryInterface $planRepository,
        OptionRepository $optionRepository,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productResourceFactory,
        CollectionFactory $categoryCollectionFactory,
        GroupRepositoryInterface $custGroupRepository,
        ProfilecollectionFactory $profileCollectionFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        DiscountFactory $dcr,
        Data $ssmHelper,
        \Abbott\Checkout\Helper\Data $checkoutHelper,
        InboundFeedHelper $workdayHelper,
        \Abbott\PriceInvGql\Helper\Data $priceInvHelper,
        MetabolicFactory $metabolicFactory,
        CustomerSession $customerSession,
        TimezoneInterface $timezoneInterface,
        MetabolicData $metabolicData
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->productFactory = $productFactory;
        $this->priceInfoFactory = $priceInfoFactory;
        $this->planRepository = $planRepository;
        $this->optionRepository = $optionRepository;
        $this->productResourceFactory = $productResourceFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->custGroupRepository = $custGroupRepository;
        $this->profileCollectionFactory = $profileCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->dcr = $dcr;
        $this->ssmHelper = $ssmHelper;
        $this->checkoutHelper = $checkoutHelper;
        $this->workdayHelper = $workdayHelper;
        $this->priceInvHelper = $priceInvHelper;
        $this->metabolicFactory = $metabolicFactory;
        $this->customerSession = $customerSession;
        $this->timezoneInterface = $timezoneInterface;
        $this->metabolicData = $metabolicData;
    }

    /**
     * Resolver
     *
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlNoSuchEntityException
     * @throws LocalizedException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->context = $context;
        try {

            $customerEmailID = $this->customerSession->getCustomer()->getEmail();
            $skus = $args['sku'];
            $skuArr = explode(",", $skus);
            $productDetails = [];
            $customerGroup = self::NLOG;
            $custGrpId = 0;
            if (array_key_exists(self::CUSTOMER_GRP, $args) && $args[self::CUSTOMER_GRP] !== '') {
                $custGrp = $args[self::CUSTOMER_GRP];
                $custGrpId = base64_decode($custGrp);
                $custGroup = $this->custGroupRepository->getById($custGrpId);
                if ($custGroup) {
                    $customerGroup = $custGroup->getCode();
                }
            }
            foreach ($skuArr as $sku) {
                $productInfo = [];
                $product = $this->productFactory->create();
                $product->load($product->getIdBySku($sku));
                $stockDetails = $this->stockRegistry->getStockItemBySku($sku);
                $productInfo['qty'] = $stockDetails['qty'];
                $productInfo['custom_order_on_call'] = $product->getData('order_on_call');
                if (($this->metabolicData->getLevelAttributeLabel($sku) == self::LEVEL) &&
                    ($customerEmailID != null) && ($product->getOrderOnCall() == self::AVAILABLE_FOR_CALL)) {
                    $currentDate = $this->timezoneInterface->date()->format('Y-m-d');
                    $data['sku'] = $sku;
                    $data['customer_email'] = $customerEmailID;
                    if ($this->metabolicData->ifExistingRecord($data)) {
                        $metabolicDataResult = $this->metabolicData->ifExistingRecord($data);
                        if (($metabolicDataResult['qty'] > 0) && ($metabolicDataResult['expiry_date'] >=
                                $currentDate)) {
                            $metabolicDataResult['qty'] = ($productInfo['qty'] < $metabolicDataResult['qty'])
                                ? $productInfo['qty'] : $metabolicDataResult['qty'];
                            $productInfo['qty'] = $metabolicDataResult['qty'];
                            $productInfo['custom_order_on_call'] = 0;
                        }
                    }
                }
                $priceInfo = $this->priceInfoFactory->create($product);
                $regularPriceAmount =  $priceInfo->getPrice(RegularPrice::PRICE_CODE)->getAmount();
                $regularPrice = $regularPriceAmount->getValue();
                $productInfo[self::SPECIAL_PRICE] = $product->getData(self::SPECIAL_PRICE);
                $options = $this->getProductCustomOptions($product);
                $productInfo['customizable_options'] = $options;
                $productInfo['product_name'] = $product->getName();
                $productInfo['flavors'] = $product->getData('product_flavor');
                $productInfo['forms'] = $product->getData('product_form');
                $productInfo['cases'] = $product->getData('case_of_product');
                $productInfo['product_brand'] = $product->getData('brand');
                $productInfo['aem_color'] = $product->getData('aem_color');
                $productInfo['categories'] = $this->getProductCategories($product);
                $optionTexts = $this->getSubscriptionGroups($product);
                $productInfo[self::GROUP_PRICE] = $this->getGroupPrice($product, $custGrpId);
                if (in_array($custGrpId, $this->priceInvHelper->getGroupMessageGroups()) &&
                    $product->getData('group_message')) {
                    $productInfo[self::GROUP_MESSAGE] = $product->getData('group_message');
                }
                $subscription = $this->getPlanDetails(
                    $product,
                    $customerGroup,
                    $optionTexts,
                    $productInfo[self::GROUP_PRICE],
                    $regularPrice
                );
                $productInfo['product_sku'] = $sku;
                $productInfo[self::ITEM_ID] = $stockDetails[self::ITEM_ID];
                $productInfo[self::PRODUCT_ID] = $stockDetails[self::PRODUCT_ID];
                $productInfo[self::STOCK_ID] = $stockDetails[self::STOCK_ID];
                $productInfo[self::IS_IN_STOCK] = $stockDetails[self::IS_IN_STOCK];
                $productInfo[self::BACKORERS] = $stockDetails[self::BACKORERS];
                $productInfo[self::PRICE] = $regularPrice;
                $productInfo['subscription_price'] = $subscription;
                $productInfo['size'] = $product->getAttributeText('size');
                $productInfo['weight'] = $product->getData('size_or_weight');
                $productInfo['need_state'] = $product->getData('need_state');
                $productDetails[] = $productInfo;
            }
            $productData['products'] = $productDetails;
            /*** Progressive subscription aler message START */
            $isProgressiveData = [];
            if ($subscription != null) {
                foreach ($subscription as $sub) {
                    $isProgressiveData[] = $sub[self::IS_PROGRESSIVE];
                }
            }
            $goldenTokenError = 0;
            $msgData = [];
            $goldenTokenMsgHtml = '';
            if (true === $this->context->getExtensionAttributes()->getIsCustomer()) {
                $customerId = $this->context->getUserId();
                $profileId = $this->getGoldenSubscription($customerId);
                if (!empty($profileId) && in_array(1, $isProgressiveData)) {
                    $goldenTokenError = 1;
                    $mysubscription = $this->storeManager->getStore()->getBaseUrl() .
                        "aw_sarp2/profile_edit/index/profile_id/" . $profileId;
                    $subscriptionLink = "<br>To change or update your subscription, visit your profile. Go to
 <a href='" . $mysubscription . "' title='my subscriptions'> my subscriptions</a>";
                    $goldenTokenMsgHtml = $this->getProgressivErrorMessage() . $subscriptionLink;
                } elseif (empty($profileId) && in_array(1, $isProgressiveData)) {
                    //add validation for ssm user for progressive product option
                    if ($this->checkoutHelper->IsSSMSubscriptionProgramEnabled() && $this->ssmHelper->isSSM()) {
                        $goldenTokenError = 1;
                        $goldenTokenMsgHtml = $this->getSSMProgressivErrorMessage();
                    }
                }
                //show message for ssm user for 10% product option
                if ($this->checkoutHelper->IsSSMSubscriptionProgramEnabled() &&
                    $this->ssmHelper->isSSM() && in_array(0, $isProgressiveData) &&
                    empty($profileId)) {
                    $goldenTokenError = 1;
                    $goldenTokenMsgHtml = $this->getSSMErrorMessage();
                }
                //message for non ssm user
                if (!$this->ssmHelper->isSSM()) {
                    $msgData = $this->showMessageForProgressiveOption($isProgressiveData);
                }
            } else {
                //magento customer not exists
                /*
                * If x-id-token cookie exists
                * then customer logged in AEM and IS-SSM user show the appropriate message
                * else customer not logged in AEM show the appropriate message
                */
                $token = $this->checkoutHelper->getXIdToken();
                if ($token && in_array(1, $isProgressiveData) &&
                    $this->checkoutHelper->IsSSMSubscriptionProgramEnabled()) {
                    $goldenTokenError = 1;
                    $goldenTokenMsgHtml = $this->getSSMProgressivErrorMessage();
                } elseif ($token == "") {
                    //message for guest user
                    $msgData = $this->showMessageForProgressiveOption($isProgressiveData, 'guest');
                }
                //message for ssm user and 10% product
                if ($token && in_array(0, $isProgressiveData) &&
                    $this->checkoutHelper->IsSSMSubscriptionProgramEnabled()) {
                    $goldenTokenError = 1;
                    $goldenTokenMsgHtml = $this->getSSMErrorMessage();
                } elseif ($token == "") {
                    //message for guest user
                    $msgData = $this->showMessageForProgressiveOption($isProgressiveData, 'guest');
                }
            }
            if (!empty($msgData)) {
                $goldenTokenError = $msgData['subscription_error'];
                $goldenTokenMsgHtml = $msgData['subscription_message_html'];
            }
            $productData['subscription_error'] = $goldenTokenError;
            $productData['subscription_message_html'] = $goldenTokenMsgHtml;
            /*** Progressive subscription aler message END */
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $productData;
    }

    /**
     * GetProductCustomOptions
     *
     * @param $product
     * @return array|void
     */
    public function getProductCustomOptions($product)
    {
        $options = [];
        $customOptions = $product->getOptions();
        if (!empty($customOptions)) {
            $options = [];
            foreach ($customOptions as $customOption) {
                $optionData = [];
                $optionData['option_id'] = $customOption->getOptionId();
                $optionData['metabolic_state'] = $customOption->getTitle();
                $optionValues = $customOption->getValues();
                $optionValuesData = [];
                foreach ($optionValues as $optionValue) {
                    $optionValuesArr['option_type_id'] = $optionValue->getOptionTypeId();
                    $optionValuesData[] = $optionValuesArr;
                }
                $optionData['option_values'] = $optionValuesData;
                $options[] = $optionData;
            }
            return $options;
        }
    }

    /**
     * GetProductCategories
     *
     * @param $product
     * @return string|void
     * @throws LocalizedException
     */
    public function getProductCategories($product)
    {
        $categoryIds = $product->getCategoryIds();
        if (count($categoryIds)) {
            $categoryArr = [];
            $categoryCollection = $this->categoryCollectionFactory->create();
            $categories = $categoryCollection->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', $categoryIds);
            foreach ($categories as $category) {
                $categoryArr[] = $category->getName();
            }
            return implode("|", $categoryArr);
        }
    }

    /**
     * GetPlanDetails
     *
     * @param $product
     * @param $customerGroup
     * @param $optionTexts
     * @param $groupPrice
     * @param $regularPrice
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getPlanDetails($product, $customerGroup, $optionTexts, $groupPrice, $regularPrice)
    {
        $subscription = [];
        if ($product->getData(self::SUBSCRIPTION_TYPE) == 2 || $product->getData(self::SUBSCRIPTION_TYPE) == 3) {
            foreach ($product->getData('aw_sarp2_subscription_options') as $subscriptionOption) {
                $plan = [];
                $planDetails = $this->planRepository->get($subscriptionOption[self::PLAN_ID]);
                $planId = $planDetails[self::PLAN_ID];
                $plan['id'] = $planId;
                $planOptionId = $subscriptionOption[self::OPTION_ID];
                $plan[self::OPTION_ID] = $planOptionId;
                $planName = $planDetails[self::NAME];
                $plan[self::IS_PROGRESSIVE] = $planDetails[self::IS_PROGRESSIVE];
                $plan[self::NAME] = $planName;
                $option = $this->optionRepository->get($planOptionId);
                if ($option->getIsAutoRegularPrice()) {
                    $planDiscount = 100 - $planDetails['regular_price_pattern_percent'];
                    if (!empty($planDetails['is_progressive']) && $planDiscount == 0) {
                        $searchCriteriaDiscount = $this->dcr->create();
                        $searchCriteriaDiscount->addFieldToFilter('months', ['eq' => 1]);
                        $discountRepo = $searchCriteriaDiscount->getFirstItem();
                        $discount = 0;
                        if ($discountRepo && $discountRepo->getDiscount()) {
                            $discount = $discountRepo->getDiscount();
                        }
                        $planDiscount = 100 - ($planDetails['regular_price_pattern_percent'] - $discount);
                    }
                    $plan[self::PERCENT] = $planDiscount;
                    $discountedPrice = number_format(
                        $regularPrice - ($regularPrice *
                            ($planDiscount /
                                100)),
                        2,
                        '.',
                        ','
                    );
                } else {
                    $plan[self::PERCENT] = 0;
                    $discountedPrice = $option->getRegularPrice();
                }
                $plan[self::PRICE] = $discountedPrice;
                if (!in_array($customerGroup, $optionTexts)) {
                    $plan[self::PERCENT] = 0;
                    $plan[self::PRICE] = empty($groupPrice) ? $regularPrice : $groupPrice;
                }
                
                $titles = $planDetails->getTitles();
                if (!empty($titles)) {
                    foreach ($titles as $title) {
                        if ($title->getStoreId() == $this->context->getExtensionAttributes()->getStore()->getId()) {
                            $plan[self::NAME] = $title->getTitle();
                        } elseif ($title->getStoreId() == 0) {
                            $plan[self::NAME] = $title->getTitle();
                        }
                    }
                }

                $subscription[] = $plan;
            }
        }
        return $subscription;
    }

    /**
     * GetSubscriptionGroups
     *
     * @param $product
     * @return array|void
     * @throws LocalizedException
     */
    public function getSubscriptionGroups($product)
    {
        $optionTexts = [];
        $attribute = $this->productResourceFactory->create()->getAttribute(self::SUBSCRIBE_GRP);
        if ($attribute->usesSource()) {
            if (!$product->getData(self::SUBSCRIBE_GRP)) {
                return $optionTexts;
            }
            $subscriptionGroups = explode(",", $product->getData(self::SUBSCRIBE_GRP));
            foreach ($subscriptionGroups as $subscriptionGroup) {
                $optionText = $attribute->getSource()->getOptionText($subscriptionGroup);
                if ($optionText) {
                    $optionTexts[] = $optionText;
                }
            }
        }
        return $optionTexts;
    }

    /**
     * GetGroupPrice
     *
     * @param $product
     * @param $custGrpId
     * @return float|void
     */
    public function getGroupPrice($product, $custGrpId)
    {
        if ($product->getTierPrices()) {
            /** @var TierPrice $tierPrice */
            foreach ($product->getTierPrices() as $tierPrice) {
                if ((int) $tierPrice->getData('customer_group_id') == (int) $custGrpId) {
                    return (float) $tierPrice->getData('value');
                }
            }
        }
    }

    /**
     * GetGoldenSubscription
     *
     * @param $customerId
     * @return mixed|string
     */
    public function getGoldenSubscription($customerId)
    {
        $profileCollectionFactory = $this->profileCollectionFactory->create();
        $profileCollectionFactory->addFieldToFilter('main_table.' . self::CUSTOMER_ID, ['eq' => $customerId]);
        $profileCollectionFactory->addFieldToFilter('main_table.status', ['neq' => 'cancelled']);
        $profileCollectionFactory->addFieldToFilter('as2plan.' . self::IS_PROGRESSIVE, ['eq' => 1]);
        $profileCollectionFactory->getSelect()->joinLeft(
            ['as2plan' => 'aw_sarp2_plan'],
            'main_table.'
            . self::PLAN_ID . ' = as2plan.' . self::PLAN_ID
        );
        $profileid = '';
        if ($profileCollectionFactory->getSize() > 0) {
            $profileid = $profileCollectionFactory->getData()[0]['profile_id'];
        }
        return $profileid;
    }

    public function getProgressivErrorMessage()
    {
        return $this->scopeConfig->getValue(
            self::PROGRESSIVE_PLAN_ERROR_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * GetSSMProgressivErrorMessage
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getSSMProgressivErrorMessage()
    {
        return $this->scopeConfig->getValue(
            self::SSM_PROGRESSIVE_PLAN_ERROR_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * GetSSMErrorMessage
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getSSMErrorMessage()
    {
        return $this->scopeConfig->getValue(
            self::SSM_TEN_PERCENT_PLAN_ERROR_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    public function getNonSSMProgressivErrorMessage()
    {
        return $this->scopeConfig->getValue(
            self::NON_SSM_PROGRESSIVE_PLAN_ERROR_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * GetGuestProgressivErrorMessage
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getGuestProgressivErrorMessage()
    {
        return $this->scopeConfig->getValue(
            self::GUEST_PROGRESSIVE_PLAN_ERROR_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    public function getNonSSMErrorMessage()
    {
        return $this->scopeConfig->getValue(
            self::NON_SSM_TEN_PERCENT_PLAN_ERROR_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * GetGuestErrorMessage
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getGuestErrorMessage()
    {
        return $this->scopeConfig->getValue(
            self::GUEST_TEN_PERCENT_PLAN_ERROR_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * ShowMessageForProgressiveOption
     *
     * @param $isProgressiveData
     * @param $type
     * @return array
     * @throws NoSuchEntityException
     */
    public function showMessageForProgressiveOption($isProgressiveData, $type = '')
    {
        $msgData = [];
        if ($this->checkoutHelper->IsSSMSubscriptionProgramEnabled() && in_array(1, $isProgressiveData)) {

            $goldenTokenMsgHtml = ($type) ?  $this->getGuestProgressivErrorMessage() :
                $this->getNonSSMProgressivErrorMessage();
            $msgData['subscription_error'] = 1;
            $msgData['subscription_message_html'] = $goldenTokenMsgHtml;
        } elseif ($this->checkoutHelper->IsSSMSubscriptionProgramEnabled() && in_array(
            0,
            $isProgressiveData
        )) {
            $goldenTokenMsgHtml = ($type) ? $this->getGuestErrorMessage() : $this->getNonSSMErrorMessage();
            $msgData['subscription_error'] = 1;
            $msgData['subscription_message_html'] = $goldenTokenMsgHtml;
        }
        return $msgData;
    }
}
