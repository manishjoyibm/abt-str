<?php

declare(strict_types=1);

namespace Abbott\KountCustomFields\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data
{
    public $customerRepository;
    public $groupRepository;
    public $storeManager;
    public const SHIPPINGMETHOD = "fraud_shippingmethod";
    public const SHIPPINGPHONE = "fraud_shippingphone";
    public const ORDERSOURCE = "fraud_ordersource";
    public const TIERGROUP = "fraud_tiergroup";
    public const SKU = "fraud_sku";
    public const NAME = "fraud_productname";
    public const QUANTITY = "fraud_skuqty";
    public const PRICE = "fraud_price";
    public const TAX = "fraud_tax";
    public const SYSTEM_GENERATED = 'fraud_system_generated';
    public const ZERO_GRAND_TOTAL_AMOUNT = 100;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param GroupRepositoryInterface $groupRepository
     * @param StoreManagerInterface $storeManager
     * @param RuleRepositoryInterface $ruleRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        GroupRepositoryInterface $groupRepository,
        StoreManagerInterface $storeManager,
        RuleRepositoryInterface $ruleRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->customerRepository = $customerRepository;
        $this->groupRepository = $groupRepository;
        $this->storeManager = $storeManager;
        $this->ruleRepository = $ruleRepository;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Method getItemValue
     *
     * @param PaymentDataObjectInterface $paymentDO
     * @param string $attribute
     * @return mixed
     */
    public function getItemValue(PaymentDataObjectInterface $paymentDO, string $attribute): mixed
    {
        $order = $paymentDO->getOrder();
        $items = $order->getItems();
        $values = [];
        foreach ($items as $item) {
            $values[] = $item[$attribute];
        }
        return substr(implode(",", $values), 0, 255);
    }

    /**
     * Method getItemValueFromOrder
     *
     * @param OrderInterface $order
     * @param string $attribute
     * @return mixed
     */
    public function getItemValueFromOrder(OrderInterface $order, string $attribute): mixed
    {
        $items = $order->getItems();
        $values = [];
        foreach ($items as $item) {
            $values[] = $item[$attribute];
        }
        return substr(implode(",", $values), 0, 255);
    }

    /**
     * Method getTierGroup
     *
     * @param PaymentDataObjectInterface $paymentDO
     * @return string
     */
    public function getTierGroup(PaymentDataObjectInterface $paymentDO): string
    {
        $order = $paymentDO->getOrder();
        $customerId = $order->getCustomerId();
        $tierGroup = '';
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $customerGroup = $this->groupRepository->getById($customer->getGroupId());
            $tierGroup = $customerGroup->getCode();
        }
        return $tierGroup;
    }

    /**
     * Method getTierGroupByOrder
     *
     * @param OrderInterface $order
     * @return string
     */
    public function getTierGroupByOrder(OrderInterface $order): string
    {
        $customerId = $order->getCustomerId();
        $tierGroup = '';
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $customerGroup = $this->groupRepository->getById($customer->getGroupId());
            $tierGroup = $customerGroup->getCode();
        }
        return $tierGroup;
    }

    /**
     * Method getOrderSource
     *
     * @param PaymentDataObjectInterface $paymentDO
     * @return string
     */
    public function getOrderSource(PaymentDataObjectInterface $paymentDO): string
    {
        $order = $paymentDO->getOrder();
        $ipAddress = $order->getRemoteIp();
        $source = "MBO";
        if ($ipAddress) {
            $storeId = $order->getStoreId();
            $source = $this->storeManager->getStore($storeId)->getWebsite()->getName();
        }
        return $source;
    }

    /**
     * Method getCustomFieldsForSarp2
     *
     * @param PaymentDataObjectInterface $paymentDO
     * @return array
     */
    public function getCustomFieldsForSarp2(PaymentDataObjectInterface $paymentDO): array
    {
        $result = [];
        $order = $paymentDO->getOrder();
        $result[self::SHIPPINGMETHOD] = $paymentDO->getPayment()->getOrder()->getShippingDescription();
        $result[self::SHIPPINGPHONE] = $order->getShippingAddress()->getTelephone();
        $result[self::ORDERSOURCE] = $this->getOrderSource($paymentDO);
        $result[self::TIERGROUP] = $this->getTierGroup($paymentDO);
        $result[self::SKU] = $this->getItemValue($paymentDO, 'sku');
        $result[self::NAME] = $this->getItemValue($paymentDO, 'name');
        $result[self::QUANTITY] = $this->getItemValue($paymentDO, 'qty_ordered');
        $result[self::PRICE] = $this->getItemValue($paymentDO, 'price');
        $result[self::TAX] = $this->getItemValue($paymentDO, 'tax_amount');
        $result[self::SYSTEM_GENERATED] = "Yes";
        return $result;
    }

    public function setAbbottUserDefinedFields(\Magento\Framework\DataObject $request, Order $order)
    {
        $ipAddress = $order->getRemoteIp();
        $source = "MBO";
        if ($ipAddress) {
            $storeId = $order->getStoreId();
            $source = $this->storeManager->getStore($storeId)->getWebsite()->getName();
        }

        $customFields = $request->getData('customFields');
        $customFields['ORDERSOURCE'] = $source;
        $customFields['SHIPPINGMETHOD'] = $order->getShippingDescription();
        $customFields['TIERGROUP'] = $this->getTierGroupByOrder($order);
        $customFields[self::NAME] = $this->getItemValueFromOrder($order, 'name');
        $customFields['ACCOUNT_NAME'] = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
        $request->setData('customFields', $customFields);
    }

    /**
     * Set Promo Details
     *
     * @param \Magento\Framework\DataObject $request
     * @param Order $order
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setPromoDetails(\Magento\Framework\DataObject $request, Order $order)
    {
        $appliedRuleIds = $order->getAppliedRuleIds();
        $websiteId = $order->getStore()->getWebsiteId();
        $customFields = $request->getData('customFields');
        if ($appliedRuleIds) {
            $appliedRuleIds = array_unique(explode(',', $order->getAppliedRuleIds()));
            foreach ($appliedRuleIds as $ruleId) {
                $rule = $this->ruleRepository->getById($ruleId);
                if ($rule->getDiscountAmount() == self::ZERO_GRAND_TOTAL_AMOUNT
                    || $rule->getDiscountAmount() == $this->getFreeShippingAmount($websiteId)
                ) {
                    $customFields['PROMO'] = $rule->getName();
                    break;
                }
            }
        } else {
            $customFields['PROMO'] = 'NA';
        }
        $request->setData('customFields', $customFields);
    }

    /**
     * Set Promo Details
     *
     * @param \Magento\Framework\DataObject $request
     * @return void
     */
    public function setPDLGuestUDF(\Magento\Framework\DataObject $request): void
    {
        $customFields = $request->getData('customFields');
        $customFields['PDL_GC'] = 'Yes';
        $request->setData('customFields', $customFields);
    }

    /**
     * Get Free Shipping Amount
     *
     * @param int|string|null $websiteCode
     * @return int|string|null
     */
    public function getFreeShippingAmount($websiteCode = null)
    {
        return $this->scopeConfig->getValue(
            'kount360/account/free_shipping_amount',
            ScopeInterface::SCOPE_WEBSITE,
            $websiteCode
        );
    }

    /**
     * Get Zero Dollar Website Code
     *
     * @param int|string|null $websiteCode
     * @return string
     */
    public function getZeroDollarWebsite($websiteCode = null)
    {
        return $this->scopeConfig->getValue(
            'kount360/account/zero_dollar_website',
            ScopeInterface::SCOPE_WEBSITE,
            $websiteCode
        );
    }

    /**
     * Check is Zero Dollar Website Enabled
     * @param int|string|null $websiteCode
     * @return bool
     */
    public function isZeroDollarWebsiteEnabled($websiteCode = null)
    {
        return $this->scopeConfig->isSetFlag(
            'kount360/account/zero_dollar_enabled',
            ScopeInterface::SCOPE_WEBSITE,
            $websiteCode
        );
    }

    /**
     * Get Channel
     *
     * @param int|string|null $websiteCode
     * @return string
     */
    public function getChannel($websiteCode = null)
    {
        return $this->scopeConfig->getValue(
            'kount360/account/channel',
            ScopeInterface::SCOPE_WEBSITE,
            $websiteCode
        );
    }
}
