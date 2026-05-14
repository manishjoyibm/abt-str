<?php

namespace Abbott\MyAccount\Plugin\Controller\Address;

use Abbott\MyAccount\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\Address\FormPost;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class FormPostPlugin
{

    public $customerSession;
    public $customerRepository;
    /**
     * @var ViewInterface
     */
    protected $view;

    protected $request;

    protected $myaccounthelper;

    /**
     * Construct function
     *
     * @param Http $request
     * @param ViewInterface $view
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param Data $myaccounthelper
     */
    public function __construct(
        Http $request,
        ViewInterface $view,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        Data $myaccounthelper
    ) {
        $this->request = $request;
        $this->view = $view;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->myaccounthelper = $myaccounthelper;
    }

    /**
     * BeforeExecute function
     *
     * @return void
     */
    public function beforeExecute()
    {
        $post = $this->request->getPostValue();
        if (!isset($post['form_key'])) {
            throw new LocalizedException(__('Invalid Form Key. Please refresh the page.'));
        }
    }

    /**
     * AfterExecute function
     *
     * @param FormPost $subject
     * @param $result
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterExecute(FormPost $subject, $result)
    {
        if ($this->myaccounthelper->getConfigGoogleAnalyticsEnable()) {
            $customerId = $this->customerSession->getId();
            $customer = $this->customerRepository->getById($customerId);
            $billingAddressId = $customer->getDefaultBilling();
            $shippingAddressId = $customer->getDefaultShipping();
            if ($this->request->getParam('id') !== null) {
                $billing = 0;
                $shipping = 0;
                if ($billingAddressId == $this->request->getParam('id')) {
                    $this->customerSession->setEditbillingsave(1);
                    $billing = 1;
                }
                if ($shippingAddressId == $this->request->getParam('id')) {
                    $this->customerSession->setEditshipingsave(1);
                    $shipping = 1;
                }
                if ($billing == 0 && $shipping == 0) {
                    $this->customerSession->setEditsave(1);
                }
            } else {
                $this->customerSession->setAddsave(1);
            }
        }
        return $result;
    }
}
