<?php


namespace Abbott\Subscriptionhistory\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Session\SessionManagerInterface;
use Abbott\Subscriptionhistory\Model\SubscriptionhistoryFactory;
use Magento\Framework\Json\Helper\Data;
use \Psr\Log\LoggerInterface;

class ChangeSubscriptionPlan extends AbstractHelper
{

    /**
     * @var SessionManagerInterface
     */
    protected SessionManagerInterface $coreSession;

    /**
     * @var Subscriptionhistory
     */
    protected Subscriptionhistory|SubscriptionhistoryFactory $subscriptionHistory;

    /**
     * @var Data
     */
    protected Data $jsonHelper;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    const IS_IMPERSONATE = 0;

    const CHANGE_SUBSCIPTION_PLAN_EVENT = 'MBO_subscription_plan_change';

    /**
     * ChangeSubscriptionPlan constructor.
     * @param SessionManagerInterface $coreSession
     */
    public function __construct(
        SessionManagerInterface $coreSession,
        SubscriptionhistoryFactory  $subscriptionhistory,
        Data $jsonHelper,
        LoggerInterface $logger
    ) {
        $this->coreSession = $coreSession;
        $this->subscriptionHistory = $subscriptionhistory;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
    }

    public function beforeChangeSubscriptionPlanLog($profile)
    {
        $oldPlan = [
                        'plan_id' => $profile->getPlanId(),
                        'plan_name' => $profile->getPlanName()
                    ];
        $this->coreSession->setData('old_plan', $oldPlan);
    }

    public function comparePlanvalueAndSave($profile)
    {
        $oldPlan = $this->coreSession->getOldPlan();
        $newPlan = [
            'plan_id' => $profile->getPlanId(),
            'plan_name' => $profile->getPlanName()
        ];

        if ($oldPlan['plan_id'] != $newPlan['plan_id']) {
            try {
                $subscriptionLog = $this->subscriptionHistory->create();
                $subscriptionLog->setSubscriptionId($profile->getIncrementId());
                $subscriptionLog->setCustomerId($profile->getCustomerId());
                $subscriptionLog->setStoreId($profile->getStoreId());
                $subscriptionLog->setIsImpersonate(self::IS_IMPERSONATE);
                $subscriptionLog->setEventName(self::CHANGE_SUBSCIPTION_PLAN_EVENT);
                $subscriptionLog->setBeforeValue($this->jsonHelper->jsonEncode($oldPlan));
                $subscriptionLog->setAfterValue($this->jsonHelper->jsonEncode($newPlan));
                $subscriptionLog->save();
                $this->coreSession->unsOldPlan();
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
