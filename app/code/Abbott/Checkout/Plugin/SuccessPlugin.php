<?php

namespace Abbott\Checkout\Plugin;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResourceModel;
use Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer;

class SuccessPlugin
{
    public $customerSession;
    public $accountHelper;
    public $storeManager;
    public $orderFactory;
    public $sgpRestriction;
    public $logger;
    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Abbott\MyAccount\Helper\Data $accountHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory,
        \Abbott\RestrictCheckout\Model\Restriction $sgpRestriction,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->accountHelper = $accountHelper;
        $this->storeManager = $storeManager;
        $this->orderFactory = $orderFactory;
        $this->sgpRestriction = $sgpRestriction;
        $this->logger = $logger;
    }

    public function afterExecute(\Magento\Checkout\Controller\Onepage\Success $subject, $result)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $cookieDomain = $this->accountHelper->getCookieRedirect();
        $publicCookieMetadata = $this->getCookieMetadataFactory()->createPublicCookieMetadata();
        $publicCookieMetadata->setPath('/');
        $publicCookieMetadata->setDomain($cookieDomain);
        $publicCookieMetadata->setHttpOnly(false);
        $publicCookieMetadata->setSecure(true);
        $publicCookieMetadata->setSameSite('Lax');
        if ($this->customerSession->getCustomerId()) {
            $this->sgpRestriction->setRestrictionDetails();
            try {
                $cartId = $this->accountHelper->getCartId($this->customerSession->getCustomer());
                $this->setCookie('abt_cartKey', $cartId, $publicCookieMetadata);
            } catch (\Exception $e) {
                $this->accountHelper->removeCookie('abt_cartKey');
                $this->logger->critical(
                    "Cart Creation Exception : ".$e->getMessage().
                    " with customerId = ".$this->customerSession->getCustomer()->getId()
                );
            }
            if ($storeId == 2) {
                $this->setCookie(
                    'abt_te',
                    $this->getOrdersCount($this->customerSession->getCustomer()),
                    $publicCookieMetadata
                );
            }
        } else {
            $this->accountHelper->removeCookie('abt_cartKey');
        }
        return $result;
    }
    public function setCookie($key, $value, $metaData)
    {
        $this->getCookieManager()->setPublicCookie($key, $value, $metaData);
    }

    private function getOrdersCount($customer)
    {
        $orders = [];
        if ($customer->getId()) {
            $orders = $this->orderFactory->create()->addFieldToFilter('customer_id', $customer->getId());
        }
        return count($orders);
    }

    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }
}
