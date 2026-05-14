<?php

namespace Abbott\MyAccount\Plugin\Model\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\EmailNotification;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class EmailNotificationPlugin
{
    /**
     *
     * @var CustomerRepositoryInterface
     */
    public $customerRepositoryInterface;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Construct function
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * Send welcome email to user if magento only and not ssm
     *
     * @param \EmailNotification $subject
     * @param \Closure $proceed
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param int|null $storeId
     * @param string|null $sendemailStoreId
     * @return mixed|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function aroundNewAccount(
        EmailNotification $subject,
        \Closure $proceed,
        CustomerInterface $customer,
        $type,
        $backUrl = '',
        $storeId = 0,
        $sendemailStoreId = null
    ) {
        if ($this->storeManager->getStore()->getCode() == \Abbott\MyAccount\Helper\Data::NEW_SIM_STORE_CODE) {
            return;
        }
         return $proceed($customer, $type, $backUrl, $storeId, $sendemailStoreId);
    }
}
