<?php

namespace Abbott\Sarp2\Controller\Profile;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;
use Aheadworks\Sarp2\Controller\Profile\AbstractProfile;


class Remove extends \Magento\Framework\App\Action\Action
{
	public $request;
 public $resultJsonFactory;
 public $customerSession;
 public $profileRepository;
 public $helper;
 public $updateSubscribe;
 public $changeProduct;
 const SUBSCIPTION_PLAN_REMOVE_PRODUCT_EVENT = "subscription_profile_remove_product";
	
    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * 
     * @param Context $context
     * @param ResultFactory $resultFactory
     * @param Session $customerSession
     * @param ProfileRepositoryInterface $profileRepository
     * @param Registry $registry
     * @param ProfileManagementInterface $profileManagement
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Abbott\Sarp2\Helper\Data $helper
     * @param \Abbott\Sarp2\Helper\ChangeSubscription $updateSubscribe
     * @param \Abbott\Sarp2\Model\ChangeProduct $changeProduct
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        Context $context, 
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,        
        Session $customerSession, 
        ProfileRepositoryInterface $profileRepository, 
        Registry $registry, 
        ProfileManagementInterface $profileManagement, 
	    \Magento\Framework\App\Request\Http $request, 
        \Abbott\Sarp2\Helper\Data $helper, 
        \Abbott\Sarp2\Helper\ChangeSubscription $updateSubscribe, 
        \Abbott\Sarp2\Model\ChangeProduct $changeProduct, 
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->profileRepository = $profileRepository;
        $this->profileManagement = $profileManagement;
        $this->helper = $helper;
        $this->updateSubscribe = $updateSubscribe;
        $this->changeProduct = $changeProduct;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute() {
        $resultJson = $this->resultJsonFactory->create();
        try {
            if ($this->customerSession->isLoggedIn()) {
                // customer login action
                $customerId = $this->customerSession->getCustomer()->getId();
                $params = $this->request->getParams();
                if (!empty($params)) {
                    $profileId = $params['profile_id'];
                    $sku = $params['sku'];
                    $profile = $this->profileRepository->get($profileId);
                    $itemCount = count($profile->getItems());
                    $canRemoveProduct = $this->helper->canRemoveProduct($profile->getPlanId(), $itemCount);
                    if (!empty($canRemoveProduct) && $profile->getCustomerId() == $customerId) {
                        $profileType = $this->changeProduct->getProfileType($profile->getPlanId());
                        if ($profileType == 0) {
                            //Remove Old Product from profile
                            $this->changeProduct->unsetOldSkuFromProfile($profileId, $sku, $profile->getStoreId());
                            //set total for subscription profile
                            $this->changeProduct->collectProfileData($profileId);
                            //send subscription update email 
                            if ($this->helper->getUpdateMailEnabled()) {
                                $this->updateSubscribe->updateSubscriptionNotification($profile->getProfileId());
                            }
                            return $resultJson->setData(['success' => true, 'message' => $this->messageManager->addSuccessMessage(__('Subscription Updated Successfully.'))]);
                        } else {
                            return $resultJson->setData(['success' => false, 'message' => $this->messageManager->addErrorMessage(__('Something Went Wrong while Subscription Update.'))]);
                        }
                    } else {
                        return $resultJson->setData(['success' => false, 'message' => $this->messageManager->addErrorMessage(__('Something Went Wrong while Subscription Update.'))]);
                    }
                } else {
                    return $resultJson->setData(['success' => false, 'message' => $this->messageManager->addErrorMessage(__('Something Went Wrong while Subscription Update.'))]);
                }
            } else {
                return $resultJson->setData(['success' => false, 'message' => $this->messageManager->addErrorMessage(__('Something Went Wrong while Subscription Update.'))]);
            }
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            return $resultJson->setData(['success' => false, 'message' => $this->messageManager->addErrorMessage(__($exception->getMessage()))]);
        } catch (\Exception $exception) {
            return $resultJson->setData(['success' => false, 'message' => $this->messageManager->addErrorMessage(__($exception->getMessage()))]);
        }
    }
}
