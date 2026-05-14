<?php

namespace Abbott\ShoppingCart\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface as ShippingMethodManager;

/**
 * Helper class for My Account:Contact Preferences
 */
class Data extends AbstractHelper
{
    public $cart;

    public $quote;
    public const XML_FEDEX_FREE_THRESHOLD_AMOUNT =
        'carriers/fedex/free_shipping_subtotal';

    /**
     * @var Config
     */
    protected $shippingMethodConfig;

    protected $storeManager;
     /**
      * @var ShippingMethodManager
      */
    private $shippingMethodManager;
    /**
     * @var productFactory
     */
    private $productFactory;

    protected $couponModel;

    protected $ruleRepository;

    public const SUB_TYPE = 'aw_sarp2_subscription_type';

    public const SUB_OPTION = 'aw_sarp2_subscription_options';

    /**
     * Construct function
     *
     * @param ProductFactory $productFactory
     * @param StoreManagerInterface $storeManager
     * @param Cart $cart
     * @param Context $context
     * @param Session $checkoutSession
     * @param Coupon $couponModel
     * @param RuleRepositoryInterface $ruleRepository
     * @param ShippingMethodManager $shippingMethodManager
     * @param Config $shippingMethodConfig
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function __construct(
        ProductFactory $productFactory,
        StoreManagerInterface $storeManager,
        Cart $cart,
        Context $context,
        Session $checkoutSession,
        Coupon $couponModel,
        RuleRepositoryInterface $ruleRepository,
        ShippingMethodManager $shippingMethodManager,
        Config $shippingMethodConfig
    ) {
        $this->cart = $cart;
        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;
        $this->quote = $checkoutSession->getQuote();
        $this->couponModel = $couponModel;
        $this->ruleRepository = $ruleRepository;
        $this->shippingMethodConfig = $shippingMethodConfig;
        $this->shippingMethodManager = $shippingMethodManager;
        parent::__construct($context);
    }

    /**
     * CheckSubscriptionId function
     *
     * @param $cartItems
     * @return true
     * @throws GraphQlInputException
     */
    public function checkSubscriptionId($cartItems)
    {
        $wrongId = true;
        foreach ($cartItems as $cartItem) {
            if (array_key_exists(self::SUB_TYPE, $cartItem['data']) && !empty($cartItem['data'][self::SUB_TYPE])) {
                $optionProduct = $this->productFactory->create();
                $optionProduct->getResource()->load(
                    $optionProduct,
                    $optionProduct->getIdBySku($cartItem['data']['sku'])
                );
                $options = $optionProduct->getData(self::SUB_OPTION);
                foreach ($options as $data) {
                    if ($data['option_id'] == $cartItem['data'][self::SUB_TYPE]) {
                        $wrongId = false;
                    }
                }
                if ($wrongId) {
                    throw new GraphQlInputException(__('Subscription id is not associated with SKU'));
                }
            }
        }
        return true;
    }

     /**
      * Get the store Id
      *
      * @return int
      * @throws NoSuchEntityException
      */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

     /**
      * Get the Module Config
      *
      * @param $path
      * @return mixed
      * @throws NoSuchEntityException
      */
    public function getModuleConfig($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get Status of Module.
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getFreeShippingAmount()
    {
        return $this->getModuleConfig(self::XML_FEDEX_FREE_THRESHOLD_AMOUNT);
    }

    /**
     * Check if Coupon is for free shipping or not.
     *
     * @param $ruleId
     * @return string|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getIsFreeShippingCoupon($ruleId)
    {
        $rule = $this->ruleRepository->getById($ruleId);
        return $rule->getSimpleFreeShipping();
    }

    /**
     * Get Free shipping details for shopping cart.
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getShippingDetails()
    {
        $totals = $this->cart->getQuote()->getSubtotal();
        if ($totals < 0) {
            $totals = 0;
        }
        $freeShip = $amount = $percentage = $freeShipping = $isSubscription = 0;
        $response = [];
        $freeShipping = $this->getFreeShippingInfo($freeShipping);
        $quoteItems = $this->quote->getAllItems();
        if (!empty($quoteItems)) {
            foreach ($quoteItems as $item):
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                if (!empty($options)) {
                        $itemPlanId = (isset($options['aw_sarp2_subscription_plan'])) ?
                            $options['aw_sarp2_subscription_plan']['plan_id'] : '';
                    if (!empty($itemPlanId)) {
                        $freeShip = 1;
                        $isSubscription = 1;
                    } else {
                        if ($totals >= $this->getFreeShippingAmount()) {
                            $freeShip = 1;
                        } else {
                            $percentage = ($totals * 100) / $this->getFreeShippingAmount();
                            $amount = number_format((float)($this->getFreeShippingAmount() - $totals), 2, '.', '');
                        }
                    }
                }
            endforeach;
        }
        if ($freeShipping && !$isSubscription) {
            $shippingDisplay['status'] = true;
            $shippingDisplay['message'] = 'Congrats! You’ve received free shipping!';
            $shippingDisplay['percentage'] = '100%';
            $shippingDisplay['percentage_value'] = '100';
            $shippingDisplay['color'] = '#267F4E';
            $shippingDisplay['is_subscription'] = $isSubscription;
        } else {
            $shippingDisplay['status'] = $freeShip ? true:false;
            $shippingDisplay['color'] = $freeShip ? '#267F4E' : '#7F7F7F';
            $shippingDisplay['message'] = $freeShip ? 'Congrats!  You qualify for FREE shipping!'
                : 'You’re $'.$amount.' away from FREE shipping!';
            $shippingDisplay['percentage'] = $freeShip ? '100%' : round($percentage).'%';
            $shippingDisplay['percentage_value'] = $freeShip ? '100' : round($percentage);
            $shippingDisplay['is_subscription'] = $isSubscription;
        }
        $response[] = $shippingDisplay;
        return $response;
    }

    /**
     * Get Free shipping details for mini cart.
     *
     * @param $totals
     * @param $isSubscription
     * @param $itemsCount
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCartShippingDetails($totals, $isSubscription, $itemsCount)
    {
        $freeShip = $amount = $percentage = $freeShipping = 0;
        $freeShipping = $this->getFreeShippingInfo($freeShipping);
        $quoteItems = $this->quote->getAllItems();
        $isSubscription = $this->getIsSubscriptionQuoteItems($quoteItems, $isSubscription);
        list($isSubscription, $freeShip, $percentage, $amount) = $this->getFreeshipDetails(
            $isSubscription,
            $totals,
            $percentage,
            $amount
        );
        if ($freeShipping && !$isSubscription) {
            $shippingDisplay['status'] = true;
            $shippingDisplay['message'] = 'Congrats! You’ve received free shipping!';
            $shippingDisplay['percentage'] = '100%';
            $shippingDisplay['percentage_value'] = '100';
            $shippingDisplay['is_subscription'] = $isSubscription;
        } else {
            $shippingDisplay['status'] = $freeShip ? true:false;
            $shippingDisplay['message'] = $freeShip ? 'Congrats!  You qualify for FREE shipping!'
                : 'You’re $'.$amount.' away from FREE shipping!';
            $shippingDisplay['percentage'] = $freeShip ? '100%' : round($percentage).'%';
            $shippingDisplay['percentage_value'] = $freeShip ? '100' : round($percentage);
            $shippingDisplay['is_subscription'] = $isSubscription;
        }
        $response[] = $shippingDisplay;
        return $response;
    }

    /**
     * @param array $quoteItems
     * @param int $isSubscription
     * @return int
     */
    public function getIsSubscriptionQuoteItems(array $quoteItems, int $isSubscription): int
    {
        if (!empty($quoteItems)) {
            foreach ($quoteItems as $item):
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                if (!empty($options)) {
                    $itemPlanId = (isset($options['aw_sarp2_subscription_plan'])) ?
                        $options['aw_sarp2_subscription_plan']['plan_id'] : '';
                    if (!empty($itemPlanId)) {
                        $isSubscription = 1;
                    }
                }
            endforeach;
        }
        return $isSubscription;
    }

    /**
     * @param int $freeShipping
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getFreeShippingInfo(int $freeShipping): int
    {
        if ($this->cart->getQuote()->getAppliedRuleIds()) {
            $freeShipping = 0;
            $appliedRuleIds = $this->cart->getQuote()->getAppliedRuleIds();
            $appliedRuleIds = explode(',', $appliedRuleIds);
            foreach ($appliedRuleIds as $ruleId) {
                $isFreeShippingCoupon = $this->getIsFreeShippingCoupon($ruleId);
                if ($isFreeShippingCoupon) {
                    $freeShipping = 1;
                }
            }
        }
        return $freeShipping;
    }

    /**
     * @param int $isSubscription
     * @param $totals
     * @param float|int $percentage
     * @param mixed $amount
     * @return int[]
     * @throws NoSuchEntityException
     */
    public function getFreeshipDetails(int $isSubscription, $totals, float|int $percentage, mixed $amount): array
    {
        if ($isSubscription) {
            $isSubscription = 1;
            $freeShip = 1;
        } else {
            if ($totals >= $this->getFreeShippingAmount()) {
                $freeShip = 1;
            } else {
                $freeShip = 0;
                $percentage = ($totals * 100) / $this->getFreeShippingAmount();
                $amount = number_format((float)($this->getFreeShippingAmount() - $totals), 2, '.', '');
            }
        }
        return [$isSubscription, $freeShip, $percentage, $amount];
    }
}
