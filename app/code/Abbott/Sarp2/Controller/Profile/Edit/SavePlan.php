<?php

namespace Abbott\Sarp2\Controller\Profile\Edit;

use Aheadworks\Sarp2\Controller\Profile\AbstractProfile;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;
use Abbott\Subscriptionhistory\Helper\Data as HistoryDataLog;

class SavePlan extends AbstractProfile
{
	public $helper;
 public $updateSubscribe;
 public $historyDataLog;
 const CHANGE_SUBSCIPTION_PLAN_EVENT = "subscription_plan_change";
	
    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @param Context $context
     * @param ProfileRepositoryInterface $profileRepository
     * @param Session $customerSession
     * @param Registry $registry
     * @param ActionPermission $actionPermission
     * @param FormKeyValidator $formKeyValidator
     * @param ProfileManagementInterface $profileManagement
     * @param Data $helper
     * @param ChangeSubscription $updateSubscribe
     */
    public function __construct(
        Context $context,
        ProfileRepositoryInterface $profileRepository,
        Session $customerSession,
        Registry $registry,
        ActionPermission $actionPermission,
        FormKeyValidator $formKeyValidator,
        ProfileManagementInterface $profileManagement,
        \Abbott\Sarp2\Helper\Data $helper,
        \Abbott\Sarp2\Helper\ChangeSubscription $updateSubscribe,
		HistoryDataLog $historyDataLog
    ) {
        parent::__construct($context, $profileRepository, $customerSession, $registry, $actionPermission);
        $this->formKeyValidator = $formKeyValidator;
        $this->profileManagement = $profileManagement;
        $this->helper = $helper;
        $this->updateSubscribe = $updateSubscribe;
		$this->historyDataLog = $historyDataLog;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        $profileId = $this->getRequest()->getParam('profile_id');
        if ($data) {
            try {
                $this->validate($data);
				$profile = $this->performSave($data);
                $this->messageManager->addSuccessMessage(__('Subscription Plan has been successfully changed.'));
                return $resultRedirect->setPath('*/*/index', ['profile_id' => $profile->getProfileId()]);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while changed the Subscription Plan.')
                );
            }
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    protected function isActionAllowed()
    {
        $profileId = $this->getProfile()->getProfileId();
        return $this->actionPermission->isEditPlanActionAvailable($profileId);
    }

    /**
     * Validate form
     *
     * @param array $data
     * @throws LocalizedException
     * @throws InputException
     */
    private function validate($data)
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            throw new LocalizedException(__('Invalid Form Key. Please refresh the page.'));
        }
    }

    /**
     * Perform save
     *
     * @param array $data
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    private function performSave($data)
    {
        $profile = $this->getProfile();
		$oldPlanId = $profile->getPlanId();
		$oldPlanName = $profile->getPlanName();
		$eventName = self::CHANGE_SUBSCIPTION_PLAN_EVENT;
		
        $profileData = $this->profileManagement
            ->changeSubscriptionPlan($profile->getProfileId(), $data['aw_sarp2_subscription_type']);        
		if(!empty($profileData) && $this->historyDataLog->getSubscriptionHistoryStatus($profileData->getStoreId()) && $oldPlanId != $profileData->getPlanId()){
			$oldData = [self::CHANGE_SUBSCIPTION_PLAN_EVENT => ['plan_id' => $oldPlanId, 'plan_name' => $oldPlanName]];
			$newData = [self::CHANGE_SUBSCIPTION_PLAN_EVENT => ['plan_id' => $profileData->getPlanId(), 'plan_name' => $profileData->getPlanName()]];
			$this->historyDataLog->prepareFrontendData($profileData, self::CHANGE_SUBSCIPTION_PLAN_EVENT, $oldData, $newData);
		}
        if ($this->helper->getUpdateMailEnabled()) {
            $this->updateSubscribe->updateSubscriptionNotification();
        }
        return $profileData;
    }
    
}
