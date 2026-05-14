<?php

namespace Abbott\Sarp2\Controller\Profile;

use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;
use Aheadworks\Sarp2\Controller\Profile\AbstractProfile;
use Abbott\Subscriptionhistory\Helper\Data as HistoryDataLog;

class Cancel extends \Aheadworks\Sarp2\Controller\Profile\Cancel
{
    public $resultJsonFactory;
    public $helper;
    public $cancelSubscribe;
    public $historyDataLog;
    public $myAccountHelper;
    const CANCEL_SUBSCIPTION_PLAN_EVENT = "subscription_profile_cancel";
    
    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;
    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param ProfileRepositoryInterface $profileRepository
     * @param Registry $registry
     * @param FormKeyValidator $formKeyValidator
     * @param ActionPermission $actionPermission
     * @param ProfileManagementInterface $profileManagement
     * @param \Abbott\Sarp2\Helper\Data $helper
     * @param \Abbott\Sarp2\Helper\ChangeSubscription $cancelSubscribe
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Session $customerSession,
        ProfileRepositoryInterface $profileRepository,
        Registry $registry,
        FormKeyValidator $formKeyValidator,
        ActionPermission $actionPermission,
        ProfileManagementInterface $profileManagement,
        \Abbott\Sarp2\Helper\Data $helper,
        \Abbott\Sarp2\Helper\ChangeSubscription $cancelSubscribe,
        \Abbott\MyAccount\Helper\Data $myAccountHelper,
		HistoryDataLog $historyDataLog
    ) {
        AbstractProfile::__construct($context, $profileRepository, $customerSession, $registry, $actionPermission);
        $this->profileManagement = $profileManagement;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->cancelSubscribe = $cancelSubscribe;
        $this->formKeyValidator = $formKeyValidator;
        $this->historyDataLog = $historyDataLog;
		$this->profileRepository = $profileRepository;
        $this->myAccountHelper = $myAccountHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultJson = $this->resultJsonFactory->create();
        $similacStoreId = $this->myAccountHelper->getNewSimilacStoreId();
        try {
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                throw new LocalizedException(__('Invalid Form Key. Please refresh the page.'));
            }
            $profile = $this->getProfile();
            $profileId = $profile->getProfileId();
            if ($profile->getCustomerId() == $this->customerSession->getCustomerId()) {
                $profileStatus = $profile->getStatus();
				$oldProfileData = [ self::CANCEL_SUBSCIPTION_PLAN_EVENT => $profile->getStatus()];
                $result = $this->profileManagement->changeStatusAction($profileId, Status::CANCELLED);
                $newProfile = $this->profileRepository->get($profileId); 
				if(!empty($result) && $this->historyDataLog->getSubscriptionHistoryStatus($newProfile->getStoreId()) && $profileStatus != $newProfile->getStatus()){
					$newProfileData = [ self::CANCEL_SUBSCIPTION_PLAN_EVENT => $newProfile->getStatus()];
					$this->historyDataLog->prepareFrontendData($newProfile, self::CANCEL_SUBSCIPTION_PLAN_EVENT, $oldProfileData, $newProfileData);
				}
            }
            $this->messageManager->addSuccessMessage(__('The subscription was successfully cancelled.'));
            if ($this->helper->getCancelMailEnabled()) {
                $this->cancelSubscribe->cancelSubscriptionNotification();
            }
            if($newProfile->getStoreId() == $similacStoreId){
                return $resultJson->setData(['success' => true, 'message' => $this->messageManager->addSuccessMessage(__('The subscription was successfully cancelled.'))]);
            }
       
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            if($newProfile->getStoreId() == $similacStoreId){
                return $resultJson->setData(['success' => false, 'message' => $this->messageManager->addErrorMessage(__($exception->getMessage()))]);
            }
       
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('Something went wrong while cancel the subscription.')
            );
            if($newProfile->getStoreId() == $similacStoreId){
                return $resultJson->setData(['success' => false, 'message' => $this->messageManager->addErrorMessage(__('Something went wrong while cancel the subscription.'))]);
            }
        }
        $resultRedirect->setPath('*/*/');

        //ANAPOLLO-7335 Starts
        $redirectUrl = $this->getRequest()->getParam('return_to');
        if (!empty($redirectUrl)) {
            $resultRedirect->setPath('customer/account');
        }
        //ANAPOLLO-7335 Ends

        return $resultRedirect;
    }
}
