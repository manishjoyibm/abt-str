<?php

namespace Abbott\MyAccount\Controller\Adminhtml\Index;

use Abbott\MyAccount\Helper\LinkData;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class ResetGigyaPassword extends \Magento\Backend\App\Action
{
    public $linkData;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Resend constructor.
     *
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeManager
     * @param LinkData $linkData
     */
    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        LinkData $linkData
    ) {

        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->linkData = $linkData;
        parent::__construct($context);
    }

    /**
     * Execute function
     *
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $customerId = $this->getRequest()->getParam('customer_id', false);
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $isPasswordReset = $this->linkData->getResetPasswordCurlResponse($customer);
            if ($isPasswordReset) {
                $this->messageManager->addSuccessMessage(__('The customer will receive an email to reset password.'));
            } else {
                $this->messageManager->addSuccessMessage(__('Something went wrong.'));
            }
            $resultRedirect->setPath('customer/index/edit', ['id' => $customerId, '_current' => true]);
        } else {
            $this->messageManager->addSuccessMessage(__('Customer not found.'));
            $resultRedirect->setPath('customer/index/index');
        }
        return $resultRedirect;
    }
}
