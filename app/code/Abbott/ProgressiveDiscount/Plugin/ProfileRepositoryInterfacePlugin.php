<?php

namespace Abbott\ProgressiveDiscount\Plugin;

use Abbott\MyAccount\Helper\Data as AccountHelper;

class ProfileRepositoryInterfacePlugin
{
    public $storeManager;
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    public $moduleDataSetup;
    private $logger;

    protected $manageSubscription;

    /**
     * @var Aheadworks\Sarp2\Api\PlanRepositoryInterface
     */
    protected $planRepository;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        \Abbott\ProgressiveDiscount\Model\ManageMonthlySubscriptionsFactory $manageSubscription,
        \Psr\Log\LoggerInterface $logger,
        \Aheadworks\Sarp2\Api\PlanRepositoryInterface $planRepo
    ) {
        $this->storeManager = $storeManager;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->manageSubscription = $manageSubscription;
        $this->logger = $logger;
        $this->planRepository = $planRepo;
    }

    /*@var \Aheadworks\Sarp2\Api\ProfileRepositoryInterface $subject
    @return  $result
     */

    public function afterSave(\Aheadworks\Sarp2\Api\ProfileRepositoryInterface $subject, $result)
    {
        $monthlySubscription = $this->manageSubscription->create()->load($result->getProfileId(), "profile_id");
        if (!$monthlySubscription->getId()) {
            if ($this->storeManager->getStore()->getStoreId() == AccountHelper::SIM_STORE_ID) {
                $this->idxTable($result->getProfileId(), '1', $result->getCustomerEmail());
            } else {
                $plan = $this->planRepository->get($result->getPlanId());
                if ($plan && $plan->getIsProgressive()) {
                    $this->idxTable($result->getProfileId(), '1', $result->getCustomerEmail());
                }
            }
        }
        return $result;
    }

    /*@param int $profileId, int $currentMonth, varchar $customerEmail
     @return void
     */
    private function idxTable($profileId, $currentMonth, $customerEmail)
    {

        $model = $this->manageSubscription->create();
        $model->setProfileId($profileId);
        $model->setCurrentMonth($currentMonth);
        $model->setCustomerEmail($customerEmail);
        $model->save();
    }
}
