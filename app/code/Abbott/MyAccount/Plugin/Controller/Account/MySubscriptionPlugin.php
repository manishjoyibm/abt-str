<?php

namespace Abbott\MyAccount\Plugin\Controller\Account;

use Abbott\MyAccount\Helper\Data;
use Aheadworks\Sarp2\Controller\Profile\Index;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\Http;

class MySubscriptionPlugin
{
    public $customerSession;
    public $accountHelper;
    public $helper;
    public $response;
    public $collectionFactory;
    /**
     * Construct function
     *
     * @param Session $customerSession
     * @param Data $accountHelper
     * @param \Abbott\CustomerTransistion\Helper\Data $helper
     * @param Http $response
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Session $customerSession,
        Data $accountHelper,
        \Abbott\CustomerTransistion\Helper\Data $helper,
        Http $response,
        CollectionFactory $collectionFactory
    ) {
        $this->customerSession = $customerSession;
        $this->accountHelper = $accountHelper;
        $this->helper = $helper;
        $this->response = $response;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * BeforeExecute function
     *
     * @param Index $subject
     * @return void
     */
    public function beforeExecute(Index $subject)
    {
        if ($this->customerSession->isLoggedIn() &&
            $this->accountHelper->getRedirectConfig('is_no_subscription_redirect_enabled')
        ) {
            $customerId = $this->customerSession->getCustomer()->getId();
            $profiles = $this->collectionFactory->create()
                ->addFieldToFilter(
                    'customer_id',
                    $customerId
                );
            if ($profiles->getSize()== 0) {
                $this->response->setRedirect(
                    $this->helper->getFailureUrl() . $this->accountHelper->getRedirectConfig('aem_no_subscription_page')
                );
            }
        }
    }
}
