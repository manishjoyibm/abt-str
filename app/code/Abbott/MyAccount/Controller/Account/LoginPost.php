<?php

namespace Abbott\MyAccount\Controller\Account;

use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResourceModel;
use Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Abbott\MyAccount\Model\MergeCart;
use Abbott\MyAccount\Helper\Data as AccountHelper;


/**
 * Post login customer action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoginPost extends \Magento\Customer\Controller\Account\LoginPost
{
    public $createEmptyCartForCustomer;
    public $cartManagement;
    public $quoteIdMaskFactory;
    public $quoteIdMaskResourceModel;
    public $quoteIdToMaskedQuoteId;
    public $storeManager;
    public $maskedQuoteIdToQuoteId;
    public $cartRepository;
    public $productFactory;
    public $orderFactory;
    public $subscriptions;
    public $mergeCartModel;
    public $sgpRestriction;
    public $customerUrl;
    const USERNAME = 'username';
    const PSWD = 'password';
    const ABT_CARTKEY = 'abt_cartKey';
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    protected $logger;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    protected $accountHelper;

    protected $customerTokenService;

    protected $checkoutSession;

    private $merged = false;
    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerUrl $customerHelperData
     * @param Validator $formKeyValidator
     * @param AccountRedirect $accountRedirect
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerUrl $customerHelperData,
        Validator $formKeyValidator,
        AccountRedirect $accountRedirect,
        AccountHelper $accountHelper,
        \Magento\Integration\Model\CustomerTokenService $customerTokenService,
        CreateEmptyCartForCustomer $createEmptyCartForCustomer,
        CartManagementInterface $cartManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResourceModel $quoteIdMaskResourceModel,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CartRepositoryInterface $cartRepository,
        ProductFactory $productFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory,
        \Aheadworks\Sarp2\Block\Customer\Subscriptions $subscriptions,
        \Magento\Checkout\Model\Session $checkoutSession,
        MergeCart $mergeCartModel,
        \Abbott\RestrictCheckout\Model\Restriction $sgpRestriction,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->accountHelper = $accountHelper;
        $this->customerTokenService = $customerTokenService;
        $this->createEmptyCartForCustomer = $createEmptyCartForCustomer;
        $this->cartManagement = $cartManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResourceModel = $quoteIdMaskResourceModel;
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
        $this->storeManager = $storeManager;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->cartRepository = $cartRepository;
        $this->productFactory = $productFactory;
        $this->orderFactory = $orderFactory;
        $this->subscriptions = $subscriptions;
        $this->mergeCartModel = $mergeCartModel;
        $this->checkoutSession = $checkoutSession;
        $this->sgpRestriction = $sgpRestriction;
        $this->logger = $logger;
        parent::__construct($context, $customerSession, $customerAccountManagement, $customerHelperData, $formKeyValidator, $accountRedirect);
    }

    /**
     * Get scope config
     *
     * @return ScopeConfigInterface
     * @deprecated 100.0.10
     */
    private function getScopeConfig()
    {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->scopeConfig;
        }
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    public function setCookie($key, $value, $metaData)
    {
        $this->getCookieManager()->setPublicCookie(
            $key,
            $value,
            $metaData
        );
    }

    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        $post = $this->getRequest()->getPostValue();


        if (!isset($post['form_key'])) {
            throw new LocalizedException(__('Invalid Form Key. Please refresh the page...'));
        }

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login[self::USERNAME]) && !empty($login[self::PSWD])) {
                try {
                    $customer = $this->customerAccountManagement->authenticate($login[self::USERNAME], $login[self::PSWD]);
                    $customerToken = $this->customerTokenService->createCustomerAccessToken($customer->getEmail(), $login[self::PSWD]);

                    $this->setCustomerInfomation($customerToken, $customer);
                } catch (EmailNotConfirmedException $e) {
                    $value = $this->customerUrl->getEmailConfirmationUrl($login[self::USERNAME]);
                    $message = __(
                        'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
                        $value
                    );
                } catch (UserLockedException $e) {
                    $message = __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                            . 'Please wait and try again later.'
                    );
                } catch (AuthenticationException $e) {
                    $message = __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                            . 'Please wait and try again later.'
                    );
                } catch (LocalizedException $e) {
                    $message = $e->getMessage();
                } catch (\Exception $e) {
                    // PA DSS violation: throwing or logging an exception here can disclose customer password
                    $this->messageManager->addError(
                        __('An unspecified error occurred. Please contact us for assistance.')
                    );
                } finally {
                    if (isset($message)) {
                        $redirectUrl = $this->getCookieManager()->getCookie('redirectUrl');
                        if ($redirectUrl) {
                            $cookieDomain = $this->accountHelper->getCookieRedirect();
                            $publicCookieMetadata = $this->getCookieMetadataFactory()->createPublicCookieMetadata();
                            $publicCookieMetadata->setPath('/');
                            $publicCookieMetadata->setDomain($cookieDomain);
                            $publicCookieMetadata->setHttpOnly(false);
                            $publicCookieMetadata->setSecure(true);
                            $publicCookieMetadata->setSameSite('Lax');
                            $this->setCookie('abt_msg', '{"type":"error","message":"' . $message . '"}', $publicCookieMetadata);
                            $resultRedirect = $this->resultRedirectFactory->create();
                            $resultRedirect->setUrl($redirectUrl);
                            return $resultRedirect;
                        } else {
                            $this->messageManager->addError($message);
                        }
                        $this->session->setUsername($login[self::USERNAME]);
                    }
                }
            } else {
                $this->messageManager->addError(__('A login and a password are required.'));
            }
        }
        return $this->accountRedirect->getRedirect();
    }


    public function setCustomerInfomation($customerToken, $customer)
    {
        $this->session->setCustomerDataAsLoggedIn($customer);
        if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
            $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
            $metadata->setPath('/');
            $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
        }
        $redirectUrl = $this->accountRedirect->getRedirectCookie();
        $customerEmail = base64_encode($customer->getEmail());
        $cookieDomain = $this->accountHelper->getCookieRedirect();
        $publicCookieMetadata = $this->getCookieMetadataFactory()->createPublicCookieMetadata();
        $publicCookieMetadata->setPath('/');
        $publicCookieMetadata->setDomain($cookieDomain);
        $publicCookieMetadata->setHttpOnly(false);
        $publicCookieMetadata->setSecure(true);
        $publicCookieMetadata->setSameSite('Lax');
        $storeId = $this->storeManager->getStore()->getId();
        $this->setCookie('abt_usr', '{"email":"' . $customerEmail . '","token":"' . $customerToken . '","fname":"' . $customer->getFirstName() . '","cgroup":"' . base64_encode($customer->getGroupId()) . '"}', $publicCookieMetadata);
        $this->sgpRestriction->setRestrictionDetails();
        $cartId = $this->getCartId($customer);
        $this->setCookie('abt_sesCartKey', $cartId, $publicCookieMetadata);
        $guestCartKey = $this->getCookieManager()->getCookie(self::ABT_CARTKEY);
        if ($storeId == AccountHelper::GLU_STORE_ID) {
            $this->setCookie('abt_te', $this->getOrdersCount($customer), $publicCookieMetadata);
            $this->convertGlucernaItems($customer);
        }
        if ($storeId == AccountHelper::SIM_STORE_ID) {
            $this->restrictSimilac($publicCookieMetadata);
        }
        $this->mergeCartModel->mergeCarts($customer, $guestCartKey, $cartId, $this->merged);
        if (!$this->merged || $this->merged) {
            $this->getCookieManager()->deleteCookie('abt_sesCartKey', $publicCookieMetadata);
            $this->setCookie(self::ABT_CARTKEY, $cartId, $publicCookieMetadata);
        }
        if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
            $this->accountRedirect->clearRedirectCookie();
            $resultRedirect = $this->resultRedirectFactory->create();
            // URL is checked to be internal in $this->_redirect->success()
            $resultRedirect->setUrl($this->_redirect->success($redirectUrl));
            return $resultRedirect;
        }
        $redirectUrl = $this->getCookieManager()->getCookie('redirectUrl');
        if ($redirectUrl) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl($redirectUrl);
            return $resultRedirect;
        }
    }


    private function convertGlucernaItems($customer)
    {
        $guestCartKey = $this->getCookieManager()->getCookie(self::ABT_CARTKEY);
        if ($guestCartKey) {
            try {
                $cartId = $this->maskedQuoteIdToQuoteId->execute($guestCartKey);
                $guestCart = $this->cartRepository->get($cartId);
                $allItems = $guestCart->getAllItems();
                $product = $this->getProductFromCart($allItems);
                if ($product) {
                    $allowTrial = (int)$product->getData('allow_trial');
                    $ordersCount = $this->getOrdersCount($customer);
                    $cart =  $this->cartManagement->getCartForCustomer($customer->getId());
                    $this->replaceItems($allowTrial, $ordersCount, $product, $cart, $guestCart);
                }
            } catch (\Exception $exception) {
                $this->messageManager->addError(
                    __('An unspecified error occurred')
                );
            }
        }
    }

    private function getCartId($customer)
    {
        $customerId = $customer->getId();
        $storeId = $this->storeManager->getStore()->getId();
        try {
            $cart = $this->cartManagement->getCartForCustomer($customerId);
        } catch (NoSuchEntityException $e) {
            $this->createEmptyCartForCustomer->execute($customerId, null);
            $cart =  $this->cartManagement->getCartForCustomer($customerId);
        }
        if ($storeId == AccountHelper::GLU_STORE_ID || $storeId == AccountHelper::SIM_STORE_ID) {
            $cart->removeAllItems();
            $cart->collectTotals()->save();
            if ($storeId == AccountHelper::GLU_STORE_ID) {
                $this->checkoutSession->loadCustomerQuote();
            }
        }
        $maskedId = $this->quoteIdToMaskedQuoteId->execute((int) $cart->getId());
        if (empty($maskedId)) {
            $quoteIdMask = $this->quoteIdMaskFactory->create();
            $quoteIdMask->setQuoteId((int) $cart->getId());
            $this->quoteIdMaskResourceModel->save($quoteIdMask);
            $maskedId = $this->quoteIdToMaskedQuoteId->execute((int) $cart->getId());
        }
        return $maskedId;
    }

    private function getProductFromCart($allItems)
    {
        $product = null;
        foreach ($allItems as $item) {
            $productId = $item->getProductId();
            $product = $this->productFactory->create()->load($productId);
            break;
        }
        return $product;
    }

    private function getOrdersCount($customer)
    {
        $orders = [];
        if ($customer->getId()) {
            $orders = $this->orderFactory->create()->addFieldToFilter('customer_id', $customer->getId());
        }
        return count($orders);
    }

    private function replaceItems($allowTrial, $ordersCount, $product, $cart, $guestCart)
    {
        if ($allowTrial == 1 && $ordersCount > 0) {
            $guestCart->removeAllItems();
            $guestCart->collectTotals()->save();
            $this->merged = true;
            $associatedSku = $product->getData('actual_trial_sku_mapping');
            $associatedProduct = $this->productFactory->create();
            $associatedProduct->load($associatedProduct->getIdBySku($associatedSku));
            $subscriptionType = [2, 3];
            $planId = null;
            if (in_array($associatedProduct->getData('aw_sarp2_subscription_type'), $subscriptionType)) {
                $option = $associatedProduct->getData('aw_sarp2_subscription_options');
                $planId = count($option) ? $option[0]['option_id'] : '';
            }
            $params = [
                'product' => $associatedProduct->getData('entity_id'),
                'aw_sarp2_subscription_type' => $planId,
                'qty'   => 1
            ];
            $request = new \Magento\Framework\DataObject();
            $request->setData($params);
            $cart->addProduct($associatedProduct, $request);
            $cart->collectTotals()->save();
        }
    }

    private function restrictSimilac($publicCookieMetadata)
    {
        $profiles = $this->subscriptions->getProfiles();
        $guestCartKey = $this->getCookieManager()->getCookie(self::ABT_CARTKEY);
        if ($profiles && count($profiles) && $guestCartKey) {
            $this->getProfileCheck($profiles, $publicCookieMetadata);
        }
    }

    private function getProfileCheck($profiles, $publicCookieMetadata)
    {
        foreach ($profiles as $profile) {
            if ($this->subscriptions->getStatusLabel($profile->getStatus()) == 'Active') {
                $this->merged = true;
                $this->messageManager->addError(
                    __('You already have an active subscription')
                );
                $this->setCookie('abt_asm', "You already have an active subscription", $publicCookieMetadata);
                return;
            }
        }
    }
}
