<?php

namespace Abbott\ProgressiveDiscount\Helper;

use Abbott\AwsLambda\Logger\Log;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use \Psr\Log\LoggerInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileSearchResultsInterface;
use Aheadworks\Sarp2\Model\ProfileFactory;
use Aheadworks\Sarp2\Model\Profile\Source\Status as ProfileStatus;
use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    public $storeManager;

    public $profileFactory;

    public $planRepository;

    public $log;
    public const IS_PROGRESSIVE = 1;

    public const PROGRESSIVE_CHECKOUT_RESTRICTED =
        'progressive_subscription_settings/progressive_subscription/restrict_cart_checkout';

    public const PROGRESSIVE_CHECKOUT_SUBSCRIPTION_ACTIVE_MESSAGE =
        'aboott_message/progressive_subscription/message_for_active_subscription';

    public const PROGRESSIVE_CHECKOUT_MULTIPLE_PRODUCT_MESSAGE =
        'aboott_message/progressive_subscription/message_for_multiple_progressive_product_in_cart';

    /**
     * Constructor
     *
     * @param StoreManagerInterface $storeManager
     * @param ProfileFactory $profileFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param PlanRepositoryInterface $planRepository
     * @param Log $log
     * @param Context $context
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProfileFactory $profileFactory,
        ScopeConfigInterface $scopeConfig,
        PlanRepositoryInterface $planRepository,
        Log $log,
        Context $context
    ) {
        $this->storeManager = $storeManager;
        $this->profileFactory = $profileFactory;
        $this->planRepository = $planRepository;
        $this->scopeConfig = $scopeConfig;
        $this->log = $log;
        parent::__construct($context);
    }

    /**
     * Check wether progressive subscription is active
     *
     * @param $customerId
     * @return boolean
     */
    public function isSubscriptionActive($customerId)
    {
        if ($customerId) {
            $profileCollection = $this->profileFactory->create()->getCollection()
                    ->addFieldToFilter('main_table.' . ProfileInterface::CUSTOMER_ID, ['eq' => $customerId])
                    ->addFieldToFilter('main_table.' . ProfileInterface::STATUS, ['neq' => ProfileStatus::CANCELLED]);
            $profileCollection->getSelect()->join(
                ['plan' => 'aw_sarp2_plan'],
                "main_table.plan_id = plan.plan_id AND plan.is_progressive = 1"
            );
            if (!empty($profileCollection->getData()) && $profileCollection->getSize() > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get wether the plan is is progressive
     *
     * @param $planId
     * @return false
     * @throws LocalizedException
     */
    public function getIsProgressive($planId)
    {
        if ($planId) {
            return $this->planRepository->get($planId)->getIsProgressive();
        }
        return false;
    }

    /**
     * CheckCartItems
     *
     * @param $quoteItems
     * @param $subStatus
     * @return bool
     */
    public function checkCartItems($quoteItems, $subStatus = '')
    {
        // if subscription is not active check for items in a cart
         $this->log->writeLog('Inside Add TO Cart progressive product');
        $progressiveItemArray = [];
        $result = [];
        if (!empty($quoteItems)) {
            $progressiveItemArray = $this->getProgressiveItemArray($quoteItems, $progressiveItemArray);
            $this->log->writeLog('progressive item : '.  print_r($progressiveItemArray, true));
            if (!empty($progressiveItemArray)) {
                $result = array_unique($progressiveItemArray);
                $this->log->writeLog('result count:'.count($result));
                if (!empty($result) && count($result) > 0 && ($subStatus == 'active' || $subStatus == '')) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * GetStoreId
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * GetSystemConfigValue
     *
     * @param $code
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getSystemConfigValue($code)
    {
        return $this->scopeConfig->getValue(
            $code,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * GetIsProgressiveCheckoutRestricted
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getIsProgressiveCheckoutRestricted()
    {
        return $this->getSystemConfigValue(self::PROGRESSIVE_CHECKOUT_RESTRICTED);
    }

    /**
     * Get the checkout message for active subscription
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getActiveSubscriptionCheckoutMessage()
    {
        return $this->getSystemConfigValue(self::PROGRESSIVE_CHECKOUT_SUBSCRIPTION_ACTIVE_MESSAGE);
    }

    /**
     * Get the checkout message for product in cart
     *
     * @return mixed
     */
    public function getProductSubscriptionCheckoutMessage()
    {
        return $this->getSystemConfigValue(self::PROGRESSIVE_CHECKOUT_MULTIPLE_PRODUCT_MESSAGE);
    }

    /**
     * GetProgressiveItemArray
     *
     * @param array $quoteItems
     * @param array $progressiveItemArray
     * @return array
     */
    public function getProgressiveItemArray(array $quoteItems, array $progressiveItemArray): array
    {
        foreach ($quoteItems as $item):
            // get item selected option
            $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
            if (!empty($options)) {
                $itemPlanId = (isset($options['aw_sarp2_subscription_plan'])) ?
                    $options['aw_sarp2_subscription_plan']['plan_id'] : '';
                if (!empty($itemPlanId)) {
                    $itemIsProgressive = $this->getIsProgressive($itemPlanId);
                    if ($itemIsProgressive) {
                        array_push($progressiveItemArray, $item->getProductId());
                    }
                }
            }
        endforeach;
        return $progressiveItemArray;
    }
}
