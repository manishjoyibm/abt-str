<?php

namespace Abbott\Checkout\Plugin\Controller\Cart;

use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Abbott\PedialyteCart\Helper\Data as PedialyteCartHelper;


class IndexPlugin
{
    public $quote;
    public $checkoutHelper;
    public $ssmHelper;
    public $response;
    public $helper;
    /**
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
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
     * @var \Abbott\PedialyteCart\Helper\Data
     */
    protected $pedialyteCartHelper;

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
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Abbott\AwsLambda\Helper\Data
     */
    protected $awsHelper;


    /**
     * @var CookieManagerInterface
     */
    protected $cookieManagerInterface;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    protected $maskedQuoteIdToQuoteId;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Abbott\SmartCart\Helper\Data
     */
    protected $smartCartHelper;


    /**
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Abbott\ProgressiveDiscount\Helper\Data $dataHelper
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Abbott\MyAccount\Helper\Data $accountHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Abbott\Checkout\Helper\Data $checkoutHelper
     * @param CookieManagerInterface $cookieManagerInterface
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Abbott\AwsLambda\Helper\Data $data
     * @param \Abbott\SmartCart\Helper\Data $smartCartHelper
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Abbott\ProgressiveDiscount\Helper\Data $dataHelper,
        \Magento\Framework\UrlInterface $urlInterface,
        \Abbott\MyAccount\Helper\Data $accountHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Abbott\Checkout\Helper\Data $checkoutHelper,
        \Abbott\Strongmoms\Helper\Data $ssmHelper,
        \Magento\Framework\App\Response\Http $response,
        \Abbott\CustomerTransistion\Helper\Data $helper,
        CookieManagerInterface $cookieManagerInterface,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Abbott\AwsLambda\Helper\Data $data,
        \Abbott\SmartCart\Helper\Data $smartCartHelper,
        PedialyteCartHelper $pedialyteCartHelper
    ) {
        $this->customerSession = $customerSession;
        $this->quote = $checkoutSession->getQuote();
        $this->checkoutSession = $checkoutSession;
        $this->dataHelper = $dataHelper;
        $this->urlInterface = $urlInterface;
        $this->accountHelper = $accountHelper;
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
        $this->checkoutHelper = $checkoutHelper;
        $this->awsHelper = $data;
        $this->ssmHelper = $ssmHelper;
        $this->response = $response;
        $this->helper = $helper;
        $this->cookieManagerInterface = $cookieManagerInterface;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->quoteFactory = $quoteFactory;
        $this->smartCartHelper = $smartCartHelper;
        $this->pedialyteCartHelper = $pedialyteCartHelper;
    }

    /**
     *
     * @param \Magento\Checkout\Controller\Cart\Index $subject
     */
    public function beforeExecute(\Magento\Checkout\Controller\Cart\Index $subject)
    {

        if (!$this->customerSession->isLoggedIn()) {
            $this->awsHelper->setStoreId($this->storeManager->getStore()->getId());
            if ($this->awsHelper->isCreateCustomerEnabled()) {
                return $this->awsHelper->initiate($this->customerSession, $this->urlInterface);
            }
            $this->accountHelper->removeCookie('redirectUrl');
            $this->customerSession->setAfterAuthUrl($this->urlInterface->getCurrentUrl());
            $storeId = $this->storeManager->getStore()->getId();
            if ($storeId == 3) {
               $this->customerSession->authenticate($this->urlInterface->getUrl('customer/account/create'));
            } else {
                if ($this->smartCartHelper->isEnabled()
                    || $this->pedialyteCartHelper->getModuleEnable()
                    || $this->smartCartHelper->canShowCart()
                ) {
                    $guestCartKey = $this->cookieManagerInterface->getCookie('abt_cartKey');
                    if ($guestCartKey) {
                        $quoteId = $this->maskedQuoteIdToQuoteId->execute($guestCartKey);
                        if ($quoteId != $this->checkoutSession->getQuoteId()) {
                            $quote = $this->quoteFactory->create()->load($quoteId);
                            if ($quote->getIsSmartCart()
                                || $quote->getIsPedialyteCart()
                                || $this->smartCartHelper->canShowCart()
                            ) {
                                $this->checkoutSession->replaceQuote($quote);
                                $this->quote = $this->checkoutSession->getQuote();
                            } else {
                                $this->customerSession->authenticate();
                            }
                        }
                    } else {
                        $this->customerSession->authenticate();
                    }
                } else {
                    $this->customerSession->authenticate();
                }

            }
        } else {
            //Check wether the logged in user is not SSM and seeting for SSM Subscription Program
            if ($this->checkoutHelper->isSSMSubscriptionProgramEnabled() && !$this->ssmHelper->isSSM()) {
                //check for cart items 10% or progressive
                $quoteItems = $this->quote->getAllItems();
                $itemAvailable = $this->checkoutHelper->checkCartItemsForPlan($quoteItems);
                if ($itemAvailable) {
                    $failureUrl = $this->helper->getFailureUrl();
                                    $cartErrorPageUrl = (!empty($this->checkoutHelper->getNonSsmCheckoutMessage())) ?
                                        $this->checkoutHelper->getNonSsmCheckoutMessage() : '';
                                    $this->response->setRedirect($failureUrl . $cartErrorPageUrl);
                }

            }
            if (!empty($this->dataHelper->getIsProgressiveCheckoutRestricted())) {
                $customerId = $this->customerSession->getCustomer()->getId();
                $quoteItems = $this->quote->getAllItems();
                // check for any progressive discount ongoing for customer
                if ($this->dataHelper->isSubscriptionActive($customerId)) {
                    $flag = $this->dataHelper->checkCartItems($quoteItems, 'active');
                    if ($flag) {
                        $message = (!empty($this->dataHelper->getActiveSubscriptionCheckoutMessage())) ?
                            $this->dataHelper->getActiveSubscriptionCheckoutMessage() : __('You already'.
                                ' have an item in your'.
                                ' cart that uses this special subscription offer');
                        $this->messageManager->addError($message);
                    }
                } else {
                    $flag = $this->dataHelper->checkCartItems($quoteItems);
                    if ($flag) {
                        $message = (!empty($this->dataHelper->getProductSubscriptionCheckoutMessage())) ?
                            $this->dataHelper->getProductSubscriptionCheckoutMessage() : __('You already have an'.
                                ' active subscription that'.
                            ' uses this special offer for an average savings of 20%');
                        $this->messageManager->addError($message);
                    }
                }
            }
        }
        //quantity validation for quoate items
        if ($this->checkoutHelper->isEnabledQuantityValidation()) {
            $quoteItems = $this->quote->getAllItems();
            $invalidQty = $this->checkoutHelper->validateItemsQuantity(
                $quoteItems,
                $this->storeManager->getStore()->getId()
            );
            if ($invalidQty) {
                 $this->messageManager->addError($invalidQty);
            }
        }
    }
}
