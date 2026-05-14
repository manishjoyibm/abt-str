<?php

namespace Abbott\Sarp2\Controller\Adminhtml\Subscription;

use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Abbott\Subscriptionhistory\Helper\Data;
use Abbott\Subscriptionhistory\Helper\HistoryMessages;

class Resume extends Action
{

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    private $engine;

    protected $_subscriptionHistory;

    /**
     * @param Context $context
     * @param ProfileManagementInterface $profileManagement
     */
    public function __construct(
        Context $context,
        ProfileManagementInterface $profileManagement,
        Engine $engine,
        Data $subscriptionHistory
    ) {
        parent::__construct($context);
        $this->profileManagement = $profileManagement;
        $this->engine = $engine;
        $this->_subscriptionHistory = $subscriptionHistory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $logs = null;
        $profileId = $this->getRequest()->getParam('profile_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($profileId) {
            try {
                $oldprofile = $this->_subscriptionHistory->getProfile($profileId);

                if ($this->_subscriptionHistory->getSubscriptionHistoryStatus($oldprofile->getStoreId())){
                    $this->_subscriptionHistory->getProfileStateBeforeData($oldprofile,HistoryMessages::MBO_PROFILE_RESUME);
                }

                $this->profileManagement->changeStatusAction($profileId, Status::ACTIVE);

                if ($this->_subscriptionHistory->getSubscriptionHistoryStatus($oldprofile->getStoreId())){
                    $newprofile = $this->_subscriptionHistory->getProfile($profileId);
                    $data[HistoryMessages::MBO_PROFILE_RESUME] = $this->_subscriptionHistory->compareProfileStatus(HistoryMessages::MBO_PROFILE_RESUME,$newprofile);
                    $logs = $this->_subscriptionHistory->saveSubscriptionHistoryLog($newprofile, HistoryMessages::MBO_PROFILE_RESUME, array(HistoryMessages::MBO_PROFILE_RESUME => HistoryMessages::MBO_PROFILE_RESUME), $data);
                }

                if($this->getRequest()->getParam('integration_test')){
                    return $logs;
                }

                $this->messageManager->addSuccessMessage(__('The subscription was activated successfully.'));
            } catch (LocalizedException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong, please try again.' . $exception->getMEssage())
                );
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
