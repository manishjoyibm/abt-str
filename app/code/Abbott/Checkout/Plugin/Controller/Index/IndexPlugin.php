<?php

namespace Abbott\Checkout\Plugin\Controller\Index;
use Abbott\PedialyteCart\Helper\Data as PedialyteCartHelper;

class IndexPlugin
{
    public $checkoutHelper;
    public $ssmHelper;
    const URL = 'checkout/cart';
    /**
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     *
     * @var \Abbott\MyAccount\Helper\Data
     */
    protected $accountHelper;

    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     *
     * @var \Abbott\RestrictCheckout\Model\Restriction
     */
    protected $sgpRestriction;

    /**
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     *
     * @var \Abbott\ProgressiveDiscount\Helper\Data
     */
    protected $dataHelper;

    /**
     *
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Abbott\AwsLambda\Helper\Data
     */
    protected $awsHelper;

    /**
     * @var \Abbott\SmartCart\Helper\Data
     */
    protected $smartCartHelper;

    /**
     *
     * @var \Abbott\PedialyteCart\Helper\Data
     */
    protected $pedialyteCartHelper;

    /**
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Abbott\MyAccount\Helper\Data $accountHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Abbott\RestrictCheckout\Model\Restriction $sgpRestriction
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Abbott\ProgressiveDiscount\Helper\Data $dataHelper
     * @param \Magento\Framework\App\Response\Http $response
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Abbott\Checkout\Helper\Data $checkoutHelper
     * @param \Abbott\AwsLambda\Helper\Data $data
     * @param \Abbott\SmartCart\Helper\Data $smartCartHelper
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $urlInterface,
        \Abbott\MyAccount\Helper\Data $accountHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Abbott\RestrictCheckout\Model\Restriction $sgpRestriction,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Abbott\ProgressiveDiscount\Helper\Data $dataHelper,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Abbott\Checkout\Helper\Data $checkoutHelper,
        \Abbott\AwsLambda\Helper\Data $data,
        \Abbott\Strongmoms\Helper\Data $ssmHelper,
        \Abbott\SmartCart\Helper\Data $smartCartHelper,
        PedialyteCartHelper $pedialyteCartHelper
    ) {
        $this->customerSession = $customerSession;
        $this->urlInterface = $urlInterface;
        $this->accountHelper = $accountHelper;
        $this->storeManager = $storeManager;
        $this->sgpRestriction = $sgpRestriction;
        $this->checkoutSession = $checkoutSession;
        $this->response = $response;
        $this->dataHelper = $dataHelper;
        $this->messageManager = $messageManager;
        $this->checkoutHelper = $checkoutHelper;
        $this->awsHelper = $data;
        $this->ssmHelper = $ssmHelper;
        $this->smartCartHelper = $smartCartHelper;
        $this->pedialyteCartHelper = $pedialyteCartHelper;
    }

    /**
     *
     * @param \Abbott\Checkout\Controller\Index\Index $subject
     * @return type
     */
    public function beforeExecute(\Abbott\Checkout\Controller\Index\Index $subject)
    {
        if (!$this->customerSession->isLoggedIn()) {

            $this->awsHelper->setStoreId($this->storeManager->getStore()->getId());
            if ($this->awsHelper->isCreateCustomerEnabled()) {
                return $this->awsHelper->initiate($this->customerSession, $this->urlInterface, false);
            }

            $this->accountHelper->removeCookie('redirectUrl');
            $this->customerSession->setAfterAuthUrl($this->urlInterface->getCurrentUrl());
            $storeId = $this->storeManager->getStore()->getId();
            if ($storeId == 3) {
                $this->customerSession->authenticate($this->urlInterface->getUrl('customer/account/create'));
            } else {
                if (($this->checkoutSession->getQuote() && $this->smartCartHelper->isEnabled()) ||
                    ($this->checkoutSession->getQuote() &&
                        $this->pedialyteCartHelper->getModuleEnable() &&
                        $this->pedialyteCartHelper->isGuestFeatureEnable())) {

                    $quote = $this->checkoutSession->getQuote();
                    if ($quote->getIsSmartCart() || $quote->getIsPedialyteCart()) {
                        // Proceed with the next steps
                    } else {
                        $this->customerSession->authenticate();
                    }
                } else {
                    $this->customerSession->authenticate();
                }
            }
        }

        if ($this->sgpRestriction->validateCustomerGroup()) {
            $orderTotal = (double) $this->sgpRestriction->getOrderTotalForCustomer();
            $orderLimit = (double) $this->sgpRestriction->getOrderLimit();
            $quoteTotal = (double) $this->checkoutSession->getQuote()->getSubtotalWithDiscount();
            if (($orderTotal + $quoteTotal) > $orderLimit) {
                $this->messageManager->addError(
                    $this->sgpRestriction->getMessage()
                );
                return $this->response->setRedirect($this->urlInterface->getUrl(self::URL));
            }
        }

        //quantity validation for quoate items
        if ($this->checkoutHelper->isEnabledQuantityValidation()) {
            $quoteItems = $this->checkoutSession->getQuote()->getAllItems();
            $invalidQty = $this->checkoutHelper->validateItemsQuantity(
                $quoteItems,
                $this->storeManager->getStore()->getId()
            );
            if ($invalidQty) {
                 return $this->response->setRedirect($this->urlInterface->getUrl(self::URL));
            }
        }

        //Check wether the logged in user is SSM ans seeting for SSM Subscription Program
        if ($this->customerSession->isLoggedIn() && $this->checkoutHelper->isSSMSubscriptionProgramEnabled()) {
            //check for product with plan available in cart
            $itemAvailable = $this->checkoutHelper->checkCartItemsForPlan($quoteItems);
            if (!$this->ssmHelper->isSSM() && $itemAvailable) {
                return $this->response->setRedirect($this->urlInterface->getUrl(self::URL));
            }
        }

        if ($this->customerSession->isLoggedIn() && !empty($this->dataHelper->getIsProgressiveCheckoutRestricted())) {
            $customerId = $this->customerSession->getCustomer()->getId();
            $quoteItems = $this->checkoutSession->getQuote()->getAllItems();
            // check for any progressive discount ongoing for customer
            if ($this->dataHelper->isSubscriptionActive($customerId)) {
                $flag = $this->dataHelper->checkCartItems($quoteItems, 'active');
                if ($flag) {
                    return $this->response->setRedirect($this->urlInterface->getUrl(self::URL));
                }
            } else {
                $flag = $this->dataHelper->checkCartItems($quoteItems);
                if ($flag) {
                    return $this->response->setRedirect($this->urlInterface->getUrl(self::URL));
                }
            }
        }
    }
}
