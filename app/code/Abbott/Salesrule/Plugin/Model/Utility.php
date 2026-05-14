<?php


namespace Abbott\Salesrule\Plugin\Model;

use Abbott\Salesrule\Helper\Data;
use Magento\Framework\DataObjectFactory;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Magento\SalesRule\Model\ResourceModel\Coupon\UsageFactory;
use Magento\SalesRule\Model\Rule\CustomerFactory;

class Utility
{

    /**
     * @var DataObjectFactory
     */
    protected $objectFactory;

    /**
     * @var Usage
     */
    protected $usageFactory;

    /**
     * @var CouponFactory
     */
    protected $couponFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Data
     */
    protected $helper;

    public const NO_COUPON = 1;

    /**
     * Utility constructor.
     *
     * @param DataObjectFactory $objectFactory
     * @param UsageFactory $usage
     * @param CouponFactory $couponFactory
     * @param CustomerFactory $customerFactory
     * @param Data $helper
     */
    public function __construct(
        DataObjectFactory $objectFactory,
        UsageFactory $usage,
        CouponFactory $couponFactory,
        CustomerFactory $customerFactory,
        Data $helper
    ) {
        $this->objectFactory = $objectFactory;
        $this->usageFactory = $usage;
        $this->couponFactory = $couponFactory;
        $this->customerFactory = $customerFactory;
        $this->helper = $helper;
    }

    /**
     * AroundCanProcessRule
     *
     * @param \Magento\SalesRule\Model\Utility $subject
     * @param callable $proceed
     * @param $rule
     * @param $address
     * @return bool|mixed
     */
    public function aroundCanProcessRule(
        \Magento\SalesRule\Model\Utility $subject,
        callable $proceed,
        $rule,
        $address
    ) {
        if ($rule->hasIsValidForAddress($address) && !$address->isObjectNew()) {
            return $rule->getIsValidForAddress($address);
        }

        /**
         * check per coupon usage limit
         */
        if ($rule->getCouponType() != \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON) {
            $couponCode = $address->getQuote()->getCouponCode();
            if (strlen($couponCode)) {
                /** @var \Magento\SalesRule\Model\Coupon $coupon */
                $coupon = $this->couponFactory->create();
                $coupon->load($couponCode, 'code');
                if ($coupon->getId()) {
                    // check entire usage limit
                    if ($coupon->getUsageLimit() && $coupon->getTimesUsed() >= $coupon->getUsageLimit()) {
                        $rule->setIsValidForAddress($address, false);
                        return false;
                    }
                    // check per customer usage limit
                    $customerId = $address->getQuote()->getCustomerId();
                    if ($customerId && $coupon->getUsagePerCustomer()) {
                        $couponUsage = $this->objectFactory->create();
                        $this->usageFactory->create()->loadByCustomerCoupon(
                            $couponUsage,
                            $customerId,
                            $coupon->getId()
                        );
                        if ($couponUsage->getCouponId() &&
                            $couponUsage->getTimesUsed() >= $coupon->getUsagePerCustomer()
                        ) {
                            $rule->setIsValidForAddress($address, false);
                            return false;
                        }
                    }
                }
            }
        }
        /**
         * check per rule usage limit
         */
        $ruleId = $rule->getId();
        if ($ruleId && $rule->getUsesPerCustomer()) {
            $customerId = $address->getQuote()->getCustomerId();
            /** @var \Magento\SalesRule\Model\Rule\Customer $ruleCustomer */
            $ruleCustomer = $this->customerFactory->create();
            $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
            if ($ruleCustomer->getId() && $ruleCustomer->getTimesUsed() >= $rule->getUsesPerCustomer()) {
                $rule->setIsValidForAddress($address, false);
                return false;
            }
        }
        $rule->afterLoad();
        /**
         * Coupon code should not get appled when address is already used against coupon code
         * Jira ANAPOLLO-2903
         */
        if ($this->checkAddressValidationEnabe($rule) && ($rule->getCouponType() != self::NO_COUPON) &&
            (!$this->helper->checkAddressUsedForPromo($address))) {
                $rule->setIsValidForAddress($address, false);
                return false;
        }
        /**
         * quote does not meet rule's conditions
         */
        if (!$rule->validate($address)) {
            $rule->setIsValidForAddress($address, false);
            return false;
        }
        /**
         * passed all validations, remember to be valid
         */
        $rule->setIsValidForAddress($address, true);
        return true;
    }

    /**
     * CheckAddressValidationEnabe
     *
     * @param $rule
     * @return bool
     */
    private function checkAddressValidationEnabe($rule)
    {
        $flag = false;
        if ($rule->getAddressValidation()) {
            $flag = true;
        }
        return $flag;
    }
}
