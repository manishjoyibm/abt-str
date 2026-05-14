<?php

namespace Abbott\Sarp2\Controller\Adminhtml\Subscription;

use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Abbott\Subscriptionhistory\Helper\Data as SubscriptionHistoryHelper;
use Abbott\Subscriptionhistory\Helper\HistoryMessages;

class Cancel extends \Aheadworks\Sarp2\Controller\Adminhtml\Subscription\Cancel
{
    public $helper;
    public $cancelSubscribe;
    const ADMIN_RESOURCE = 'Aheadworks_Sarp2::subscriptions';

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    protected $_subscriptionHistory;

    /**
     * @param Context $context
     * @param Data $helper
     * @param ProfileManagementInterface $profileManagement
     * @param ChangeSubscription $cancelSubscribe
     */
    public function __construct(
        Context $context,
        \Abbott\Sarp2\Helper\Data $helper,
        \Aheadworks\Sarp2\Api\ProfileManagementInterface $profileManagement,
        \Abbott\Sarp2\Helper\ChangeSubscription $cancelSubscribe,
        SubscriptionHistoryHelper $subscriptionHistory
    ) {
        $this->helper = $helper;
        $this->profileManagement = $profileManagement;
        $this->cancelSubscribe = $cancelSubscribe;
        $this->_subscriptionHistory = $subscriptionHistory;
        parent::__construct($context,$profileManagement);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $log = null;
        $profileId = $this->getRequest()->getParam('profile_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($profileId) {
            try {
                $oldprofile = $this->_subscriptionHistory->getProfile($profileId);

                if($this->_subscriptionHistory->getSubscriptionHistoryStatus($oldprofile->getStoreId())){
                    $this->_subscriptionHistory->getProfileStateBeforeData($oldprofile,HistoryMessages::MBO_PROFILE_CANCELLED);
                }

                $this->profileManagement->changeStatusAction($profileId, Status::CANCELLED);

                if($this->_subscriptionHistory->getSubscriptionHistoryStatus($oldprofile->getStoreId())){
                    $newprofile = $this->_subscriptionHistory->getProfile($profileId);
                    $data[HistoryMessages::MBO_PROFILE_CANCELLED] = $this->_subscriptionHistory->compareProfileStatus(HistoryMessages::MBO_PROFILE_CANCELLED,$newprofile);
                    $log = $this->_subscriptionHistory->saveSubscriptionHistoryLog($newprofile, HistoryMessages::MBO_PROFILE_CANCELLED, array(HistoryMessages::MBO_PROFILE_CANCELLED => HistoryMessages::MBO_PROFILE_CANCELLED), $data);
                }

                if($this->getRequest()->getParam('integration_test')){
                    return $log;
                }

                $this->messageManager->addSuccessMessage(__('The subscription was successfully cancelled.'));
                if ($this->helper->getCancelMailEnabled()) {
                    $this->cancelSubscribe->cancelSubscriptionNotification();
                }
            } catch (LocalizedException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while cancel the subscription.')
                );
            }
        }
        return $resultRedirect->setPath('*/*/');
    }

}
