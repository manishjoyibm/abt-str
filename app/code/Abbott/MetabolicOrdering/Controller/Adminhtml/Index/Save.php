<?php
namespace Abbott\MetabolicOrdering\Controller\Adminhtml\Index;

use Abbott\MetabolicOrdering\Api\Data\MetabolicInterface;
use Abbott\MetabolicOrdering\Api\Data\MetabolicInterfaceFactory;
use Abbott\MetabolicOrdering\Helper\Data;
use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Abbott\MetabolicOrdering\Api\MetabolicOrderingRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Abbott\MetabolicOrdering\Model\MetabolicFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Backend\App\Action;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Save extends Action
{
    protected $timezoneInterface;

    /**
     * @var helper
     */
    protected $helper;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var MetabolicInterfaceFactory
     */
    private $metabolicFactory;

    /**
     * @var MetabolicRepositoryInterface
     */
    private $metabolicRepository;

    /**
     * @var AccountManagementInterface
     */
    private $customerAccountManagement;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var MetabolicFactory
     */
    protected $metabolicModelFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Constructor
     *
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param TimezoneInterface $timezoneInterface
     * @param MetabolicOrderingRepositoryInterface $metabolicRepository
     * @param AccountManagementInterface $customerAccountManagement
     * @param Data $helper
     * @param MetabolicFactory $metabolicModelFactory
     * @param MetabolicInterfaceFactory $metabolicFactory
     * @param StoreManagerInterface $storeManager
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        CustomerRepositoryInterface $customerRepository,
        TimezoneInterface $timezoneInterface,
        MetabolicOrderingRepositoryInterface $metabolicRepository,
        AccountManagementInterface $customerAccountManagement,
        Data $helper,
        MetabolicFactory $metabolicModelFactory,
        MetabolicInterfaceFactory $metabolicFactory,
        StoreManagerInterface $storeManager,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->customerRepository = $customerRepository;
        $this->timezoneInterface = $timezoneInterface;
        $this->metabolicModelFactory = $metabolicModelFactory;
        $this->metabolicFactory = $metabolicFactory;
        $this->metabolicRepository = $metabolicRepository;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context);
    }

    /**
     * Check if user has permissions to access this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Abbott_MetabolicOrdering::save");
    }

    /**
     * Save metabolic action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        $customerId = $this->getCustomerIdByEmail($this->getRequest()->getParam('customer_email'));
        if ($customerId && empty($this->helper->ifExistingRecord($data)) || isset($data['entity_id'])) {
            $request['customer_id'] = $data['customer_id'] = $customerId;
            $request['admin_user'] = $data['admin_user'] = $this->helper->getCurrentUser()->getUsername();
            if (empty($data['expiry_date'])) {
                $currentDate = $this->timezoneInterface->date()->format('Y-m-d');
                $newEndingDate = date("Y-m-d", strtotime(date("Y-m-d", strtotime($currentDate)) . " + 1 year"));
                $data['expiry_date'] = $newEndingDate;
            }

            $this->performSave($data);
            $sku = $this->getRequest()->getParam('sku');
            $qty = $this->getRequest()->getParam('qty');
            $request['comment'] =  'Product with sku: '.$sku.' is assigned with qty:' .$qty;
            $this->helper->updateComments($request);
            $this->messageManager->addSuccess(__('Record saved'));
        } else {
            $this->messageManager->addError(__('Customer already exists with this record'));
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Perform save
     *
     * @param $data
     * @return void
     * @throws Exception
     */
    private function performSave($data)
    {
        $metabolicData = $this->metabolicModelFactory->create(); 
        $newQty = $data['qty']; 
        $newExpiry = strtotime($data['expiry_date']);  
        $targetDate = strtotime($this->helper->getTargetDate()); 
        $threshouldQty =$this->helper->thresholdQty();   
        if (isset($data['entity_id'])) {
            $metabolicData->load($data['entity_id']);
            $oldQty = $metabolicData->getQty();
            $oldExpiry = strtotime($metabolicData->getExpiryDate());
        }
        
        $metabolicData->setData($data);
         if(isset($oldQty) && ($newQty > $threshouldQty))
            {
               $metabolicData->setData('threshold_email_sent', 0);
            }
        if(isset($oldExpiry) && ($newExpiry > $targetDate))
            {
                $metabolicData->setData('expiry_email_sent', 0);
            }
        $metabolicData->save();
    }

    /**
     * To Fetch customer id by email
     *
     * @param string $email
     * @return int|void
     */
    public function getCustomerIdByEmail(string $email)
    {
        try {
            $customerData = $this->customerRepository->get($email);
            if ($customerData) {
                return (int)$customerData->getId();
            }
        } catch (Exception $e) {
            $this->messageManager->addException($e, __('Customer with this email does not exists'));
        }
    }
}
