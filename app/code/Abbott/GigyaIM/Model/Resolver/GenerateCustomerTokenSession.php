<?php

declare(strict_types=1);

namespace Abbott\GigyaIM\Model\Resolver;

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Registration;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResourceModel;
use Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer;
use Abbott\MyAccount\Model\MergeCart;
use Abbott\AwsLambda\Logger\Log as Logger;
use Abbott\MyAccount\Helper\Data as AccountHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Abbott\GigyaIM\Helper\Data as GigyaHelper;
use Abbott\GigyaIM\Helper\CustomerHelper;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory as ProfilecollectionFactory;
use Magento\Rma\Model\ResourceModel\Rma\Collection as Rmacollection;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Customers Token resolver, used for GraphQL request processing.
 */
class GenerateCustomerTokenSession implements ResolverInterface
{
    public $cognitoId;
    public $cognitoEmail;
    public $accountHelper;
    public const ABT_CARTKEY = 'abt_cartKey';

    public const ABT_SESCARTKEY = 'abt_sesCartKey';
    public const SIMILAC_STORE_CODE = 'new_similac';
    public const PEDIALYTE_STORE_CODE = 'pedialyte';

    /**
     * @var Session
     */
    protected Session $session;

    /**
     * @var Registration
     */
    protected Registration $registration;

    /**
     * @var GigyaHelper
     */
    protected GigyaHelper $gigyaHelper;

    /**
     * @var CustomerHelper
     */
    protected CustomerHelper $customerHelper;

    /**
     * @var TokenModelFactory
     */
    protected TokenModelFactory $tokenModelFactory;

    /**
     * @var CartManagementInterface
     */
    protected CartManagementInterface $cartManagement;

    /**
     * @var CreateEmptyCartForCustomer
     */
    protected CreateEmptyCartForCustomer $createEmptyCartForCustomer;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    protected QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId;

    /**
     * @var QuoteIdMaskFactory
     */
    protected QuoteIdMaskFactory $quoteIdMaskFactory;

    /**
     * @var QuoteIdMaskResourceModel
     */
    protected QuoteIdMaskResourceModel $quoteIdMaskResourceModel;

    /**
     * @var MergeCart
     */
    protected MergeCart $mergeCartModel;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private CookieMetadataFactory $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private CookieManagerInterface $cookieManagerInterface;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $orderCollectionFactory;

    /**
     * @var CollectionFactory
     */
    private ProfilecollectionFactory|CollectionFactory $profileCollectionFactory;

    /**
     * @var Collection
     */
    protected Rmacollection|Collection $rmaCollection;

    /**
     * @var String
     */
    protected string $gigyaUID;

    /**
     * @var String
     */
    protected string $gigyaEmail;

    /**
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $customerRepository;

    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;

    /**
     * GenerateCustomerTokenSession constructor.
     *
     * @param Session $customerSession
     * @param Registration $registration
     * @param GigyaHelper $gigyaHelper
     * @param TokenModelFactory $tokenModelFactory
     * @param CartManagementInterface $cartManagement
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     * @param CreateEmptyCartForCustomer $createEmptyCartForCustomer
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteIdMaskResourceModel $quoteIdMaskResourceModel
     * @param MergeCart $mergeCartModel
     * @param Logger $logger
     * @param AccountHelper $accountHelper
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param CookieManagerInterface $cookieManagerInterface
     * @param CollectionFactory $orderCollectionFactory
     * @param ProfilecollectionFactory $profileCollectionFactory
     * @param Rmacollection $rmaCollection
     * @param CustomerHelper $customerHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Session $customerSession,
        Registration $registration,
        GigyaHelper $gigyaHelper,
        TokenModelFactory $tokenModelFactory,
        CartManagementInterface $cartManagement,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
        CreateEmptyCartForCustomer $createEmptyCartForCustomer,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResourceModel $quoteIdMaskResourceModel,
        MergeCart $mergeCartModel,
        Logger $logger,
        AccountHelper $accountHelper,
        CookieMetadataFactory $cookieMetadataFactory,
        CookieManagerInterface $cookieManagerInterface,
        CollectionFactory $orderCollectionFactory,
        ProfilecollectionFactory $profileCollectionFactory,
        Rmacollection $rmaCollection,
        CustomerHelper $customerHelper,
        CustomerRepositoryInterface $customerRepository,
        CheckoutSession $checkoutSession
    ) {
        $this->session = $customerSession;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->gigyaHelper = $gigyaHelper;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->registration = $registration;
        $this->cartManagement = $cartManagement;
        $this->createEmptyCartForCustomer = $createEmptyCartForCustomer;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResourceModel = $quoteIdMaskResourceModel;
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
        $this->mergeCartModel = $mergeCartModel;
        $this->logger = $logger;
        $this->accountHelper = $accountHelper;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManagerInterface = $cookieManagerInterface;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->profileCollectionFactory = $profileCollectionFactory;
        $this->rmaCollection = $rmaCollection;
        $this->customerHelper = $customerHelper;
        $this->customerRepository = $customerRepository;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Decode the Gigya id token
     *
     * @param string $idToken
     * @return mixed
     * @throws GraphQlInputException
     */
    public function decodeGigyaIdToken(string $idToken): mixed
    {
        try {
            $token = json_decode(
                base64_decode(
                    str_replace('_', '/', str_replace('-', '+', explode('.', $idToken)[1]))
                )
            );
        } catch (Exception $e) {
            $token = false;
        }
        if (!$token) {
            throw new GraphQlInputException(__('The "gigya_id_token" format is incorrect.'));
        }
        return $token;
    }

    /**
     * Set the cookie
     *
     * @param string $key
     * @param string $value
     * @param PublicCookieMetadata $metaData
     * @return void
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    public function setCookie(string $key, string $value, \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata $metaData): void
    {
        $this->cookieManagerInterface->setPublicCookie(
            $key,
            $value,
            $metaData
        );
    }

    /**
     * Validate GigyaId Email
     *
     * @param array $args
     * @return void
     * @throws GraphQlInputException
     */
    protected function validateGigyaIdEmail(array $args): void
    {
        if (empty($args['input']['gigya_id_token'])) {
            throw new GraphQlInputException(__('Specify the "gigya_id_token" value.'));
        }
        if (empty($args['input']['gigya_user']['email'])) {
            throw new GraphQlInputException(__('Specify the customer email value.'));
        }
        $idToken = $args['input']['gigya_id_token'];

        //Decode the id token
        $gigyaInfo = $this->decodeGigyaIdToken($idToken);
        $this->gigyaUID = $gigyaInfo->sub;
        $this->gigyaEmail = $args['input']['gigya_user']['email'];
    }

    /**
     * @throws GraphQlInputException
     */
    protected function validateCognitoEmail($args): void
    {
        if (empty($args['input']['cognito_details']['email'])) {
            throw new GraphQlInputException(__('Specify the customer email value.'));
        }
        if (empty($args['input']['cognito_details']['cognito_id'])) {
            throw new GraphQlInputException(__('Specify the cognito id value.'));
        }
        $this->cognitoEmail = $args['input']['cognito_details']['email'];
        $this->cognitoId = $args['input']['cognito_details']['cognito_id'];
    }

    /**
     * Validate gigya user and generate Magento session & token
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @throws Exception
     * @return boolean
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): ?bool
    {

        $customerId = $context->getUserId();
        if ($customerId) {
            throw new GraphQlInputException(__('Customer has an active token.'));
        }
        $this->logger->setScope((int)$context->getExtensionAttributes()->getStore()->getId());
        $store_code = $context->getExtensionAttributes()->getStore()->getCode();

        if ($store_code == self::SIMILAC_STORE_CODE) {
            return $this->handleGigyaStore($context, $args);
        } elseif ($store_code == self::PEDIALYTE_STORE_CODE) {
            return $this->handleCognitoStore($context, $args);
        } else {
            $this->logger->writeLog('Cognito or Gigya is not enabled for the requested website/store');
            throw new GraphQlInputException(__('Cognito or Gigya is not enabled for the website/store'));
        }
    }

    /**
     * @throws GraphQlInputException
     * @throws LocalizedException
     */
    private function handleGigyaStore($context, $args)
    {
        if ($this->gigyaHelper->isGigyaEnabledForWebsite()) {
            $this->validateGigyaIdEmail($args);
            $gigyaUid = $this->gigyaUID;
            if ($this->session->isLoggedIn()) {
                $this->session->logout();
            }
            if ($this->registration->isAllowed()) {
                return $this->checkGigyaCustomer($context, $gigyaUid);
            }
        } else {
            $this->logger->writeLog('Gigya is not enabled for the requested website/store');
            throw new GraphQlInputException(__('Gigya is not enabled for the website/store'));
        }
    }

    /**
     * @throws GraphQlInputException
     */
    private function handleCognitoStore($context, $args)
    {
        if ($this->gigyaHelper->isCognitoEnabledForWebsite()) {
            $this->validateCognitoEmail($args);
            $cognitoId = $this->cognitoId;
            if ($this->session->isLoggedIn()) {
                $this->session->logout();
            }
            if ($this->registration->isAllowed()) {
                return $this->checkCognitoCustomer($context, $cognitoId);
            }
        } else {
            $this->logger->writeLog('Cognito is not enabled for the requested website/store');
            throw new GraphQlInputException(__('Cognito is not enabled for the website/store'));
        }
    }


/**
 * Get Card ID function
 *
 * @param $customer
 * @return string
 * @throws NoSuchEntityException
 * @throws AlreadyExistsException
 * @throws CouldNotSaveException
 */
    protected function getCartId($customer): string
    {
        $customerId = (int)$customer->getId();
        try {
            $cart = $this->cartManagement->getCartForCustomer($customerId);
        } catch (NoSuchEntityException $e) {
            $this->createEmptyCartForCustomer->execute($customerId, null);
            $cart = $this->cartManagement->getCartForCustomer($customerId);
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

/**
 * Get Customer Order function
 *
 * @param $customerId
 * @return int
 */
    public function getCustomerOrder($customerId)
    {
        $customerOrder = $this->orderCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId);
        return $customerOrder->getSize();
    }

/**
 * Get Customer Returns function
 *
 * @param $customerId
 * @return int
 */
    public function getCustomerReturns($customerId)
    {
        $rmacollection = $this->rmaCollection->addFieldToFilter('customer_id', $customerId)->load();
        return $rmacollection->getSize();
    }

    /**
     * Get Customer Subscription function
     *
     * @param $customerId
     * @return int
     */
    public function getCustomerSubscription($customerId)
    {
        $profileCollection = $this->profileCollectionFactory->create();
        $profileCollection->addFieldToFilter('customer_id', ['eq' => $customerId]);
        $profileCollection->addFieldToFilter('status', ['neq' => 'cancel']);
        return $profileCollection->getSize();
    }

    /**
     * Check GigyaCustomer and Set Cookies.
     *
     * @param $context
     * @param $gigyaUid
     * @return bool
     */
    public function checkGigyaCustomer($context, $gigyaUid)
    {
        try {
            $websiteId = (int)$context->getExtensionAttributes()->getStore()->getWebsiteId();
            $customer = $this->customerHelper->findGigyaCustomer(
                $gigyaUid,
                $this->gigyaEmail,
                $websiteId
            );
            if (!$customer) {
                $guestCartKey = $this->cookieManagerInterface->getCookie(self::ABT_CARTKEY);
                $maskedQuoteId = $this->customerHelper->setCart(
                    $this->gigyaEmail,
                    $websiteId,
                    $guestCartKey
                );
                if ($maskedQuoteId) {
                    $cookieDomain = $this->accountHelper->getCookieRedirect();
                    $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
                    $publicCookieMetadata->setPath('/');
                    $publicCookieMetadata->setDomain($cookieDomain);
                    $publicCookieMetadata->setHttpOnly(false);
                    $publicCookieMetadata->setSecure(true);
                    $publicCookieMetadata->setSameSite('Lax');
                    $this->setCookie(self::ABT_CARTKEY, $maskedQuoteId, $publicCookieMetadata);
                }
                return false;
            }
            $this->customerHelper->deleteSsmCart(
                $this->gigyaEmail,
                $websiteId
            );
            $attribute = $customer->getCustomAttribute('gigya_uid');
            if (!$attribute) {
                $customer->setCustomAttribute('gigya_uid', $gigyaUid);
                $this->customerRepository->save($customer);
            }
            $this->session->regenerateId();
            $customerId = $customer->getId();
            $customerToken = $this->tokenModelFactory
                ->create()
                ->createCustomerToken($customerId)
                ->getToken();

            if ($this->cookieManagerInterface->getCookie('mage-cache-sessid')) {
                $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                $metadata->setPath('/');
                $this->cookieManagerInterface->deleteCookie('mage-cache-sessid', $metadata);
            }
            $cookieDomain = $this->accountHelper->getCookieRedirect();
            $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $publicCookieMetadata->setPath('/');
            $publicCookieMetadata->setDomain($cookieDomain);
            $publicCookieMetadata->setHttpOnly(false);
            $publicCookieMetadata->setSecure(true);
            $publicCookieMetadata->setSameSite('Lax');
            //Add order page and subscription page parameter
            $orderpage = 0;
            $returnpage = 0;
            $subscriptionpage = 0;
            if ($this->getCustomerOrder($customerId) > 0) {
                $orderpage = 1;
            }
            if ($this->getCustomerReturns($customerId) > 0) {
                $returnpage = 1;
            }
            if ($this->getCustomerSubscription($customerId) > 0) {
                $subscriptionpage = 1;
            }
            $cookieVal = [
                //'customer_id' => $customerId,
                'token' => $customerToken,
                'fname' => $customer->getFirstName(),
                'cgroup' => base64_encode($customer->getGroupId()),
                'link_hide' => [
                    'returns' => $returnpage
                ],
                'magento_page' => [
                    'orders' => $orderpage,
                    'subscriptions' => $subscriptionpage
                ]
            ];
            $this->setCookie(
                'abt_usr',
                json_encode($cookieVal),
                $publicCookieMetadata
            );
            $this->setCookie(
                'abt_mage_sessid',
                'yes',
                $publicCookieMetadata
            );
            $this->session->setCustomerDataAsLoggedIn($customer);
            //ANAPOLLO-7411 Code Starts
            $this->session->setCustomerId($customer->getId());
            //ANAPOLLO-7411 Code Ends
            $guestCartKey = $this->cookieManagerInterface->getCookie(self::ABT_CARTKEY);
            $customerCartId = $this->getCartId($customer);
            if ($customerCartId) {
                $this->setCookie(self::ABT_CARTKEY, $customerCartId, $publicCookieMetadata);
            }
            if ($guestCartKey && $customerCartId && $guestCartKey != $customerCartId) {
                $this->mergeCartModel->mergeCarts($customer, $guestCartKey, $customerCartId, false);
            }
            //ANAPOLLO-7411 Code Starts
            $this->checkoutSession->clearQuote();
            $this->checkoutSession->loadCustomerQuote();
            //ANAPOLLO-7411 Code Ends
            return true;
        } catch (Exception $e) {
            $this->logger->writeLog(sprintf('User UID=%s logged to Gigya', $gigyaUid));
            $this->logger->writeLog("Gigya error response: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check CognitoCustomer and Set Cookies.
     *
     * @param $context
     * @param $cognitoId
     * @return bool
     */
    public function checkCognitoCustomer($context, $cognitoId): bool
    {
        try {
            $websiteId = (int)$context->getExtensionAttributes()->getStore()->getWebsiteId();
            $customer = $this->customerHelper->findCognitoCustomer(
                $cognitoId,
                $this->cognitoEmail,
                $websiteId
            );
            if (!$customer) {
                $guestCartKey = $this->cookieManagerInterface->getCookie(self::ABT_CARTKEY);
                $maskedQuoteId = $this->customerHelper->setCart(
                    $this->cognitoEmail,
                    $websiteId,
                    $guestCartKey
                );
                if ($maskedQuoteId) {
                    $cookieDomain = $this->accountHelper->getCookieRedirect();
                    $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
                    $publicCookieMetadata->setPath('/');
                    $publicCookieMetadata->setDomain($cookieDomain);
                    $publicCookieMetadata->setHttpOnly(false);
                    $publicCookieMetadata->setSecure(true);
                    $publicCookieMetadata->setSameSite('Lax');
                    $this->setCookie(self::ABT_CARTKEY, $maskedQuoteId, $publicCookieMetadata);
                }
                return false;
            }
            $this->customerHelper->deleteSsmCart(
                $this->cognitoEmail,
                $websiteId
            );
            $attribute = $customer->getCustomAttribute('cognito_id');
            if (!$attribute) {
                $customer->setCustomAttribute('cognito_id', $cognitoId);
                $this->customerRepository->save($customer);
            }
            $this->session->regenerateId();
            $customerId = $customer->getId();
            $customerToken = $this->tokenModelFactory
                ->create()
                ->createCustomerToken($customerId)
                ->getToken();

            if ($this->cookieManagerInterface->getCookie('mage-cache-sessid')) {
                $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                $metadata->setPath('/');
                $this->cookieManagerInterface->deleteCookie('mage-cache-sessid', $metadata);
            }
            $cookieDomain = $this->accountHelper->getCookieRedirect();
            $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $publicCookieMetadata->setPath('/');
            $publicCookieMetadata->setDomain($cookieDomain);
            $publicCookieMetadata->setHttpOnly(false);
            $publicCookieMetadata->setSecure(false);
            $publicCookieMetadata->setSameSite('Lax');
            //Add order page and subscription page parameter
            $orderpage = 0;
            $returnpage = 0;
            $subscriptionpage = 0;
            if ($this->getCustomerOrder($customerId) > 0) {
                $orderpage = 1;
            }
            if ($this->getCustomerReturns($customerId) > 0) {
                $returnpage = 1;
            }
            if ($this->getCustomerSubscription($customerId) > 0) {
                $subscriptionpage = 1;
            }
            $cookieVal = [
                //'customer_id' => $customerId,
                'token' => $customerToken,
                'fname' => $customer->getFirstName(),
                'cgroup' => base64_encode((string)$customer->getGroupId()),
                'link_hide' => [
                    'returns' => $returnpage
                ],
                'magento_page' => [
                    'orders' => $orderpage,
                    'subscriptions' => $subscriptionpage
                ]
            ];
            $this->setCookie(
                'abt_usr',
                json_encode($cookieVal),
                $publicCookieMetadata
            );
            $this->setCookie(
                'abt_mage_sessid',
                'yes',
                $publicCookieMetadata
            );
            $this->session->setCustomerDataAsLoggedIn($customer);
            $guestCartKey = $this->cookieManagerInterface->getCookie(self::ABT_CARTKEY);
            $this->cookieManagerInterface->deleteCookie(self::ABT_CARTKEY);
            $customerCartId = $this->getCartId($customer);
            if ($customerCartId) {
                $this->setCookie(self::ABT_CARTKEY, $customerCartId, $publicCookieMetadata);
            }
            if ($guestCartKey && $customerCartId && $guestCartKey != $customerCartId) {
                $this->mergeCartModel->mergeCarts($customer, $guestCartKey, $customerCartId, false);
            } else {
                $this->checkoutSession->loadCustomerQuote();
            }
            return true;

        } catch (Exception $e) {
            $this->logger->writeLog(sprintf('User UID=%s logged to Cognito', $cognitoId));
            $this->logger->writeLog("Cognito error response: " . $e->getMessage());
            return false;
        }
    }
}
