<?php


namespace Abbott\Subscriptionhistory\Plugin\Sarp2\Model;

use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierPool;
use Aheadworks\Sarp2\Engine\Profile\ActionFactory;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Abbott\Subscriptionhistory\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Abbott\Sarp2\Controller\Profile\Edit\SavePlan;

class ProfileManagement
{

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var Data
     */
    private $changePlan;

    /**
     * @var ApplierPool
     */
    private $applierPool;

    const MBO_CHANGE_SUBSCIPTION_PLAN_EVENT = "MBO_subscription_plan_change";

    const MBO_PROFILE_PLAN_CHANGE_SESSION = 'MBO_profile_plan_change';

    /**
     * ProfileManagement constructor.
     * @param ProfileRepositoryInterface $profileRepository
     * @param ActionFactory $actionFactory
     * @param Data $changeplan
     * @param ApplierPool $applierPool
     */
    public function __construct(
        ProfileRepositoryInterface $profileRepository,
        ActionFactory $actionFactory,
        Data $changeplan,
        ApplierPool $applierPool
    ) {
        $this->profileRepository = $profileRepository;
        $this->actionFactory = $actionFactory;
        $this->changePlan = $changeplan;
        $this->applierPool = $applierPool;
    }

    /**
     * @param \Aheadworks\Sarp2\Model\ProfileManagement $subject
     * @param callable $proceed
     * @param $profileId
     * @param $newPlanId
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws LocalizedException
     */
    public function aroundChangeSubscriptionPlan(
        \Aheadworks\Sarp2\Model\ProfileManagement $subject,
        callable $proceed,
        $profileId,
        $newPlanId
    ) {
        $profile = $this->profileRepository->get($profileId);

        if ($this->changePlan->getSubscriptionHistoryStatus($profile->getStoreId())) {

            $this->changePlan->beforeChangeSubscriptionPlanLog(
                $profile->getProfileId(),
                self::MBO_PROFILE_PLAN_CHANGE_SESSION
            );
        }

        $action = $this->actionFactory->create(
            [
                'type' => ActionInterface::ACTION_TYPE_CHANGE_PLAN,
                'data' => ['new_plan_id' => $newPlanId]
            ]
        );

        $applier = $this->applierPool->getApplier(ActionInterface::ACTION_TYPE_CHANGE_PLAN);
        $validationResult = $applier->validate($profile, $action);
        if (!$validationResult->isValid()) {
            throw new LocalizedException(__($validationResult->getMessage()));
        }
        $applier->apply($profile, $action);

        if ($this->changePlan->getSubscriptionHistoryStatus($profile->getStoreId())) {
            $profile = $this->profileRepository->get($profileId);
            $sessionNames = [self::MBO_PROFILE_PLAN_CHANGE_SESSION => self::MBO_PROFILE_PLAN_CHANGE_SESSION];
            $this->changePlan->comparePlanvalueAndSave(
                $profile,
                self::MBO_CHANGE_SUBSCIPTION_PLAN_EVENT,
                $sessionNames
            );
        }

        return $profile;
    }
}
