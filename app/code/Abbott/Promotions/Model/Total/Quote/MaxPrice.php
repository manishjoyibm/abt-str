<?php
namespace Abbott\Promotions\Model\Total\Quote;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Coupon;

class MaxPrice extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{

    protected $saleRule;

    protected $coupon;

    /**
     * Construct function
     *
     * @param RuleRepositoryInterface $saleRule
     * @param Coupon $coupon
     */
    public function __construct(
        RuleRepositoryInterface $saleRule,
        Coupon $coupon
    ) {
        $this->saleRule = $saleRule;
        $this->coupon = $coupon;
    }

    /**
     * Collect function
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this|MaxPrice
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        //Fix for discount applied twice
        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }
        if ($quote->getCouponCode()) {
            $couponCode = $quote->getCouponCode();
            $currentCoupon = $this->coupon->loadByCode($couponCode);
            if ($currentCoupon->getId()) {
                $ruleId = $currentCoupon->getRuleId();
                $rule = $this->saleRule->getById($ruleId);
                $ruleData = $rule->__toArray();
                $getEnableMaxRule = $ruleData['enable_max_price_rule'] ?? null;
                if ($getEnableMaxRule) {
                    $quoteItems = $quote->getAllVisibleItems();
                    $discountAmount = $this->getItemsDiscountAmount($quoteItems, $ruleId);
                    $label = null;
                    parent::collect($quote, $shippingAssignment, $total);
                    $discountAmount = -$discountAmount;
                    $appliedCartDiscount = 0;
                    if ($total->getDiscountDescription()) {
                        $appliedCartDiscount = $total->getDiscountAmount();
                        $label = $total->getDiscountDescription();
                    }
                    $total->setDiscountDescription($label);
                    $total->setDiscountAmount($discountAmount);
                    $total->setBaseDiscountAmount($discountAmount);
                    $total->setSubtotalWithDiscount($total->getSubtotal() + $discountAmount);
                    $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() + $discountAmount);
                    if (isset($appliedCartDiscount)) {
                        $total->addTotalAmount($this->getCode(), $discountAmount - $appliedCartDiscount);
                        $total->addBaseTotalAmount($this->getCode(), $discountAmount - $appliedCartDiscount);
                    } else {
                        $total->addTotalAmount($this->getCode(), $discountAmount);
                        $total->addBaseTotalAmount($this->getCode(), $discountAmount);
                    }

                }
            }
        }
        return $this;
    }

    /**
     * GetItemsDiscountAmount function
     *
     * @param $quoteItems
     * @param $ruleId
     * @return int|mixed
     */
    public function getItemsDiscountAmount($quoteItems, $ruleId)
    {
        $discountAmount = 0;
        $priceArray = [];
        if (count($quoteItems)) {
            foreach ($quoteItems as $quoteItem) {
                $itemAppliedRuleIds = $quoteItem->getAppliedRuleIds();
                $itemAppliedRuleIdsArray = explode(",", $itemAppliedRuleIds);
                if (in_array($ruleId, $itemAppliedRuleIdsArray)) {
                    $price = $quoteItem->getProduct()->getPrice();
                    $sku = $quoteItem->getProduct()->getSku();
                    $priceArray[$price] = $sku;

                }
            }
        }
        if (count($priceArray) > 1) {
            $maxPriceItem = max(array_keys($priceArray));
            $discountAmount = $maxPriceItem;
        }
        return $discountAmount;
    }
}
