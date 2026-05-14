<?php
namespace Abbott\CartRuleMessage\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\SalesRule\Api\Data\RuleInterface;
use Abbott\CartRuleMessage\Helper\Data as dataHelper;

class CustomConfigProvider implements ConfigProviderInterface
{
    /**
     * @var DataHelper
     */
    protected $dataHelper;
    /**
     * @var coupon
     */
    protected $coupon;
    /**
     * @var saleRule
     */
     protected $saleRule;
    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    protected $sessionFactory;
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;
    /**
     * @param \Magento\Checkout\Model\SessionFactory $sessionFactory
     * @param \Magento\Quote\Model\QuoteFactory      $quoteFactory
     */
    public function __construct(
        \Magento\Checkout\Model\SessionFactory $sessionFactory,
        \Magento\SalesRule\Model\Rule $saleRule,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\SalesRule\Model\Coupon $coupon,
        DataHelper $dataHelper
    ) {
        $this->sessionFactory = $sessionFactory;
        $this->saleRule = $saleRule;
        $this->coupon = $coupon;
        $this->quoteFactory = $quoteFactory;
        $this->dataHelper = $dataHelper;
    }

    public function getConfig()
    {
        $additionalVariables['custom_message'] = '';
        if ($this->dataHelper->getEnable()) {
            $quoteId = $this->sessionFactory->create()->getQuote()->getId();
            $quote = $this->quoteFactory->create()->loadActive($quoteId);
            $couponCode = $quote->getCouponCode();
            if ($couponCode) {
                $ruleId = $this->coupon->loadByCode($couponCode)->getRuleId();
                if ($ruleId) {
                    $rule = $this->saleRule->load($ruleId);
                    $additionalVariables['custom_message'] = $rule->getCheckoutMessage();
                }
            }
        }
        return $additionalVariables;
    }
}
