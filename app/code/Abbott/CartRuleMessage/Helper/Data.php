<?php

namespace Abbott\CartRuleMessage\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Helper For message configuration
 */
class Data extends AbstractHelper
{
    public $saleRule;
    public $coupon;
    const XML_PATH_ENABLE = 'cartrulemessage/settings/enable';
    const XML_PATH_ENABLE_MBO = 'cartrulemessage/settings/enable_admin';


    protected $storeManager;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var InlineTranslation
     */
    protected $inlineTranslation;

    /**
     * @param Context               $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\SalesRule\Model\Rule $saleRule,
        \Magento\SalesRule\Model\Coupon $coupon,
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->saleRule = $saleRule;
        $this->coupon = $coupon;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

     /**
      * Get the store Id
      *
      * @return int
      */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
     /**
      * Get the Module Config
      *
      * @param  $path
      * @param  int $storeId
      * @return mixed
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
     */
    public function getEnable()
    {
        return $this->getModuleConfig(self::XML_PATH_ENABLE);
    }

    /**
     * Get Status of Module for Admin.
     *
     * @return mixed
     */
    public function getAdminEnable()
    {
        return $this->getModuleConfig(self::XML_PATH_ENABLE_MBO);
    }

     /**
      * Get checkout message from cart rule.
      *
      * @return string
      */
    public function getCheckoutMessage($couponCode)
    {
        $message = null;
        if ($couponCode) {
            $ruleId = $this->coupon->loadByCode($couponCode)->getRuleId();
            if ($ruleId) {
                $rule = $this->saleRule->load($ruleId);
                if ($rule->getCheckoutMessage()) {
                    $message=$rule->getCheckoutMessage();
                }
            }
        }
        return $message;
    }
}
