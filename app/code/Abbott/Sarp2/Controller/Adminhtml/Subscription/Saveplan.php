<?php

namespace Abbott\Sarp2\Controller\Adminhtml\Subscription;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterfaceFactory;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;


class Saveplan extends Action
{
    public $updateSubscribe;
    public $helper;
    public $authorization;
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Sarp2::subscriptions';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var ProfileInterfaceFactory
     */
    private $profileFactory;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ProfileInterfaceFactory $profileFactory
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(
        Context $context,
        ProfileInterfaceFactory $profileFactory,
        ProfileRepositoryInterface $profileRepository,
        ProfileManagementInterface $profileManagement,
        \Abbott\Sarp2\Helper\Data $helper,
        \Abbott\Sarp2\Helper\ChangeSubscription $updateSubscribe,
        \Magento\Framework\AuthorizationInterface $authorization
    ) { 
        parent::__construct($context);
        $this->profileFactory = $profileFactory;
        $this->profileRepository = $profileRepository;
        $this->profileManagement = $profileManagement;
        $this->updateSubscribe = $updateSubscribe;
        $this->helper = $helper;
        $this->authorization = $authorization;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $profileId = (int)$this->getRequest()->getParam('profile_id');
        $subscriptionplan = $this->getRequest()->getParam('aw_sarp2_subscription_type');
        
        if($subscriptionplan)
        {
            try {
            $profile = $this->performSave($profileId,$subscriptionplan);
            $this->messageManager->addSuccessMessage(__('Subscription Plan has been successfully changed.')); 
            } catch (LocalizedException $e) { //echo "sd"; die;
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {//echo "mm"; die;
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while changed the Subscription Plan.')
                );
            }
        }
       
        $resultRedirect = $this->resultRedirectFactory->create();
        
        return $resultRedirect->setPath('*/*/view/profile_id/'.$profileId);

    }

    /**
     * Perform save
     *
     * @param array $data
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    private function performSave($profile_id,$subscriptionplan)
    {
        $profileData = $this->profileManagement
            ->changeSubscriptionPlan($profile_id, $subscriptionplan);
        if ($this->helper->getUpdateMailEnabled()) {  
            $this->updateSubscribe->updateSubscriptionNotification();
        }
        return $profileData;
    }

    /**
     * Added resource for access
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
