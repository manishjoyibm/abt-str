<?php

namespace Abbott\Sarp2\Model\Sales\Order\Item\Option;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterfaceFactory;
use Aheadworks\Sarp2\Model\Plan\TitleResolver as PlanTitleResolver;
use Magento\Framework\Api\DataObjectHelper;
use Abbott\MyAccount\Helper\Data as AccountHelper;

class Processor extends \Aheadworks\Sarp2\Model\Sales\Order\Item\Option\Processor
{
    public $storeManager;
    const SUBSCRIPTION_PLAN = 'aw_sarp2_subscription_plan';
    const LABEL = 'label';
    const VALUE = 'value';
    
    /**
     * @var PlanInterfaceFactory
     */
    private $planFactory;

    /**
     * @var PlanTitleResolver
     */
    private $planTitleResolver;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;
    
    /**
     * @param PlanInterfaceFactory $planFactory
     * @param PlanTitleResolver $planTitleResolver
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        PlanInterfaceFactory $planFactory,
        PlanTitleResolver $planTitleResolver,
        DataObjectHelper $dataObjectHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($planFactory, $planTitleResolver, $dataObjectHelper);
        $this->planFactory = $planFactory;
        $this->planTitleResolver = $planTitleResolver;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->storeManager = $storeManager;
    }
    
    /**
     * Get detailed subscription options
     *
     * @param array $options
     * @param int $storeId
     * @param bool $isAdmin
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCompleteSubscriptionOptions($orderDate, $options, $storeId, $isAdmin = false)
    {
        $subscriptionOptions = [];
        if ($this->isSubscription($options)) {
            $planData = $options[self::SUBSCRIPTION_PLAN];
            /** @var PlanInterface $plan */
            $plan = $this->planFactory->create();
            $this->dataObjectHelper->populateWithArray($plan, $planData, PlanInterface::class);

            $planTitle = $isAdmin ? $plan->getName() : $this->planTitleResolver->getTitle($plan, $storeId);
            
            if ($this->storeManager->getStore()->getId() == AccountHelper::GLU_STORE_ID) {
                $subscriptionOptions[] = [
                    self::LABEL => __('Subscription Plan'),
                    self::VALUE => $planTitle,
                    self::SUBSCRIPTION_PLAN => $plan->getPlanId(),
                ];
            } else {
                $subscriptionOptions[] = [
                    self::LABEL => __('Subscription Start Date'),
                    self::VALUE => $orderDate,
                    self::SUBSCRIPTION_PLAN => $plan->getPlanId(),
                ];
                $subscriptionOptions[] = [
                    self::LABEL => __('Billing Period'),
                    self::VALUE => $planTitle,
                    self::SUBSCRIPTION_PLAN => $plan->getPlanId(),
                ];
            }
        }

        return $subscriptionOptions;
    }
}
