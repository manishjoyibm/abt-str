<?php
 
namespace Abbott\Sarp2\Controller\Profile\Edit;

use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Controller\Profile\AbstractProfile;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;
use Abbott\Sarp2\Helper\Data;
use Abbott\Sarp2\Helper\ChangeSubscription;
use Abbott\Subscriptionhistory\Helper\Data as HistoryDataLog;
use Magento\Directory\Model\RegionFactory as RegionFactory;
/**
 * Class SaveAddress
 * @package Aheadworks\Sarp2\Controller\Profile\Edit
 */
class SaveAddress extends AbstractProfile
{
    public $historyDataLog;
    public $regionFactory;
    const CHANGE_SUBSCIPTION_ADDRESS_EVENT = "subscription_shipping_address_change";
    
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    private $helper;

    private $updateSubscribe;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ProfileRepositoryInterface $profileRepository
     * @param Session $customerSession
     * @param Registry $registry
     * @param ActionPermission $actionPermission
     * @param FormKeyValidator $formKeyValidator
     * @param CustomerRepositoryInterface $customerRepository
     * @param ProfileManagementInterface $profileManagement
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProfileRepositoryInterface $profileRepository,
        Session $customerSession,
        Registry $registry,
        ActionPermission $actionPermission,
        FormKeyValidator $formKeyValidator,
        CustomerRepositoryInterface $customerRepository,
        ProfileManagementInterface $profileManagement,
        Data $helper,
        ChangeSubscription $updateSubscribe,
		HistoryDataLog $historyDataLog,
        RegionFactory $regionFactory
    ) {
        parent::__construct($context, $profileRepository, $customerSession, $registry, $actionPermission);
        $this->resultPageFactory = $resultPageFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerRepository = $customerRepository;
        $this->profileManagement = $profileManagement;
        $this->helper = $helper;
        $this->updateSubscribe = $updateSubscribe;
        $this->historyDataLog = $historyDataLog;
        $this->regionFactory = $regionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                $this->validate();
                $profile = $this->performSave($data);

                if ($this->helper->getUpdateMailEnabled()) {
                    $this->updateSubscribe->updateSubscriptionNotification();
                }
                
                $this->messageManager->addSuccessMessage(__('Shipping Address has been successfully changed.'));
                return $resultRedirect->setPath('*/*/index', ['profile_id' => $profile->getProfileId()]);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while changed the Address.'));
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
        return $this->actionPermission->isEditAddressActionAvailable($profileId);
    }

    /**
     * Validate form
     *
     * @throws LocalizedException
     */
    private function validate()
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    private function performSave($data)
    {
        $profile = $this->getProfile();
        $oldProfileAddressId = $profile->getShippingAddress()->getCustomerAddressId();
        $customerAddressId = isset($data['customer_address_id']) ? $data['customer_address_id'] : 0;
        $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
        /** @var AddressInterface $address */
        $address = $this->getAddressById($customer, $customerAddressId);

        $result = $this->profileManagement->changeShippingAddress($profile->getProfileId(), $address);
        if(!empty($result) && $oldProfileAddressId!= $customerAddressId && $customerAddressId!=0 && $this->historyDataLog->getSubscriptionHistoryStatus($profile->getStoreId())){
			$oldAddress = $this->getAddressById($customer, $oldProfileAddressId);
			$oldStreet = array_values($oldAddress->getStreet());
            $oldStreetName = implode(" ", $oldStreet);
            $newStreet = array_values($address->getStreet());
            $newStreetName = implode(" ", $newStreet);
            
            $oldRegion = $this->getRegionName($oldAddress->getRegionId());
            $newRegion = $this->getRegionName($address->getRegionId());
                       
            $oldData[self::CHANGE_SUBSCIPTION_ADDRESS_EVENT] = [ "address_id" => $oldProfileAddressId, "street" => $oldStreetName, "city" => $oldAddress->getCity(), "region" => $oldRegion,"country" => $oldAddress->getCountryId(),"postcode" =>$oldAddress->getPostcode() ];
			$newData[self::CHANGE_SUBSCIPTION_ADDRESS_EVENT] = [ "address_id" => $customerAddressId, "street" => $newStreetName, "city" => $address->getCity(), "region" => $newRegion,"country" => $address->getCountryId(),"postcode" => $address->getPostcode() ];
			
			$this->historyDataLog->prepareFrontendData($result, self::CHANGE_SUBSCIPTION_ADDRESS_EVENT, $oldData, $newData);		
		}
        return $result;
    }

    /**
     * Get address by id
     *
     * @param CustomerInterface $customer
     * @param int $addressId
     * @return AddressInterface|null
     * @throws NoSuchEntityException
     */
    private function getAddressById(CustomerInterface $customer, $addressId)
    {
        foreach ($customer->getAddresses() as $address) {
            if ($address->getId() == $addressId) {
                return $address;
            }
        }
        throw NoSuchEntityException::singleField('addressId', $addressId);
    }
    
    /**
     * Get region name
     *
     * @param int $regionId
     * @return string
     */
    protected function getRegionName($regionId){
        $region = $this->regionFactory->create()->load($regionId);
        $regionData = $region->getData();
        if(!empty($regionData)){
            return $regionData['name'];
        }
        return $regionId;
    }
}
