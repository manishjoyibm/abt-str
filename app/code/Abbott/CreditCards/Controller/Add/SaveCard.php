<?php

namespace Abbott\CreditCards\Controller\Add;

use Braintree\Result\Error;
use Braintree\Result\Successful;
use Braintree\Transaction;
use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use PayPal\Braintree\Gateway\Config\Config;
use Abbott\CreditCards\Model\Adapter\BraintreeAdapterFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Abbott\Sarp2\Helper\Data as AbbottSarpHelper;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\RegionFactory;
use Abbott\CreditCards\Model\AddressPaymentTokenLink;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Json\Helper\Data as Helper;
use Psr\Log\LoggerInterface;
use Abbott\CreditCards\Model\Method\Logger;
use Abbott\Sarp2\Model\PaymentChangeLogManager;

class SaveCard extends \Magento\Framework\App\Action\Action
{
    public $paymentTokenFactory;
    public $customerSession;
    public $paymentTokenRepository;
    public $encryptor;
    public $resultJsonFactory;
    public $addressRepository;
    public $addressDataFactory;
    public $paymentTokenManagement;
    public $regionFactory;
    public $linkAddress;
    public $serializer;
    public $logger;
    public const CODE = 'braintree';
    public const SUCCESS = 'success';
    public const FAILURE = 'failure';
    public const COMPANY = 'company';
    public const COUNTRY_ID = 'country_id';
    public const REGION_ID = 'region_id';
    public const FIRSTNAME = 'firstname';
    public const STREET = 'street';
    public const LASTNAME = 'lastname';
    public const POSTCODE = 'postcode';
    public const TELEPHONE = 'telephone';
    public const FAILURE_MSG = "Card authentication failed";
    public const DUPLICATE = "duplicate_card";
    public const KOUNT_MID = 'payment/braintree/kount_id';
    public const REQUEST_INFO = 'request';
    public const RESPONSE_INFO = 'response';
    public const SUCCESS_MESSAGE = 'Your payment method has been saved.
     A temporary charge of up to $1.00 may appear in your transaction history. Please see FAQ for more information.';
    public const SANDBOX_UNSUCCESSFUL_AMOUNT = '2001';
    public const AMOUNT = '0.1';


    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var BraintreeAdapterFactory
     */
    private BraintreeAdapterFactory $adapterFactory;

    /**
     * @var SessionManagerInterface
     */
    private SessionManagerInterface $session;

    /**
     * @var boolean
     *
     */
    private $isVisible;

    /**
     * @var Logger
     */
    private Logger $customLogger;

    /**
     * @var PaymentChangeLogManager
     */
    protected PaymentChangeLogManager $paymentChangeLogManager;

    /**
     * @var AbbottSarpHelper as AbbottSarpHelper;
     */
    protected AbbottSarpHelper $abbottSarpHelper;

    /**
     * @var Helper
     */
    private Helper $helper;


    /**
     * @param Context $context
     * @param SerializerInterface $serializer
     * @param Config $config
     * @param BraintreeAdapterFactory $adapterFactory
     * @param SessionManagerInterface $session
     * @param PaymentTokenFactoryInterface $paymentTokenFactory
     * @param Session $customerSession
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param EncryptorInterface $encryptor
     * @param AddressInterfaceFactory $addressDataFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @param RegionFactory $regionFactory
     * @param AddressPaymentTokenLink $linkAddress
     * @param JsonFactory $resultJsonFactory
     * @param Helper $helper
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $customLogger
     * @param PaymentChangeLogManager $paymentChangeLogManager
     * @param AbbottSarpHelper $abbottSarpHelper
     */
    public function __construct(
        Context $context,
        SerializerInterface $serializer,
        Config $config,
        BraintreeAdapterFactory $adapterFactory,
        SessionManagerInterface $session,
        PaymentTokenFactoryInterface $paymentTokenFactory,
        Session $customerSession,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        EncryptorInterface $encryptor,
        AddressInterfaceFactory $addressDataFactory,
        AddressRepositoryInterface $addressRepository,
        PaymentTokenManagementInterface $paymentTokenManagement,
        RegionFactory $regionFactory,
        AddressPaymentTokenLink $linkAddress,
        JsonFactory $resultJsonFactory,
        Helper $helper,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        Logger $customLogger,
        PaymentChangeLogManager $paymentChangeLogManager,
        AbbottSarpHelper $abbottSarpHelper
    ) {
        $this->config = $config;
        $this->adapterFactory = $adapterFactory;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->session = $session;
        $this->customerSession = $customerSession;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->encryptor = $encryptor;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->regionFactory = $regionFactory;
        $this->linkAddress = $linkAddress;
        $this->serializer = $serializer;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->customLogger = $customLogger;
        $this->paymentChangeLogManager = $paymentChangeLogManager;
        $this->abbottSarpHelper = $abbottSarpHelper;
        parent::__construct($context);
    }

    /**
     * Execute function
     *
     * @return Json|ResultInterface|ResponseInterface
     * @throws AlreadyExistsException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(): Json|ResultInterface|ResponseInterface
    {
        $request = $this->helper->jsonDecode($this->getRequest()->getContent());
        if (!$this->validateForm($request)) {
            $resultJson = $this->resultJsonFactory->create();
            $this->messageManager->addError("Empty fields in the request");
            $resultJson->setData("empty_fields");
            return $resultJson;
        }
        return $request['isEdit'] ? $this->editCard($request) : $this->addCard($request);
    }

    /**
     * Validate  Form
     *
     * @param $request
     * @return bool
     */
    private function validateForm($request): bool
    {
        $fields = [self::FIRSTNAME,self::LASTNAME,'city',self::STREET,self::REGION_ID,self::COUNTRY_ID,self::POSTCODE];
        foreach ($fields as $value) {
            if (!$request[$value]) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get Kount Merchant Id
     *
     * @return mixed
     */
    public function getBtKountMerchantId(): mixed
    {
        return $this->scopeConfig->getValue(self::KOUNT_MID);
    }


    /**
     * Add new Card
     *
     * @param $request
     * @return Json
     * @throws AlreadyExistsException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function addCard($request): Json
    {
        $response['object'] = [];
        $resultJson = $this->resultJsonFactory->create();
        $result = $this->getTransactionResult($request);
        $customerId = $this->customerSession->getCustomer()->getId();
        // Logging request data to file
        $this->customLogger->debug(self::REQUEST_INFO, $request);
        if ($result->success) {
            $transaction = $result->transaction;
            $this->adapterFactory->create($this->session->getStoreId())->void($transaction->id);
            $token = $transaction->creditCardDetails->token;
            if (empty($token)) {
                $this->messageManager->addError(self::FAILURE_MSG);
                return $resultJson->setData(self::FAILURE);
            }
            $this->checkAddedCard($resultJson, $customerId, $transaction, $token, $request);
        } else {
            $this->messageManager->addError(self::FAILURE_MSG);
            $resultJson->setData(self::FAILURE);
            try {
                $this->paymentChangeLogManager->validateFailedPaymentChanges($customerId);
            } catch (ValidationException $e) {
                $this->messageManager->addError($e->getMessage());
                $resultJson->setData($e->getMessage());
            }

            $this->paymentChangeLogManager->addRecord($customerId, true);

        }
        $response['response'] = (array)$result;
        $this->customLogger->debug(self::RESPONSE_INFO, $response);

        return $resultJson;
    }

    /**
     * Check if card is duplicate
     *
     * @param $token
     * @return int|null
     */
    private function checkIfDuplicate($token): ?int
    {
        $existingId = null;
        $tokenDuplicate = $this->paymentTokenManagement->getByPublicHash(
            $token->getPublicHash(),
            $token->getCustomerId()
        );
        if (!empty($tokenDuplicate)) {
            $existingId = $tokenDuplicate->getEntityId();
            $this->isVisible = $tokenDuplicate->getIsVisible();
        }
        return $existingId;
    }


    /**
     * Edit Card
     *
     * @param $request
     * @return Json|mixed
     * @throws AlreadyExistsException
     * @throws LocalizedException
     */
    private function editCard($request): mixed
    {
        $response = [];
        $resultJson = $this->resultJsonFactory->create();
        $paymentTokenSaved = 1;
        $publicHash = $request['paymentToken'];
        $customerId = $this->customerSession->getCustomer()->getId();
        $paymentToken = $this->paymentTokenManagement->getByPublicHash($publicHash, $customerId);
        if (array_key_exists("nonce", $request)) {
            $result = $this->getTransactionResult($request);
            $this->customLogger->debug(self::REQUEST_INFO, $request);
            $response['response'] = (array)$result;
            $this->customLogger->debug(self::RESPONSE_INFO, $response);
            if ($result->success) {
                $transaction = $result->transaction;
                $this->adapterFactory->create($this->session->getStoreId())->void($transaction->id);
                $token = $transaction->creditCardDetails->token;
                if (empty($token)) {
                    $this->messageManager->addError(self::FAILURE_MSG);
                    return $resultJson->setData(self::FAILURE);
                }
                $resultArr = $this->checkEditedCard(
                    $resultJson,
                    $customerId,
                    $paymentToken,
                    $transaction,
                    $paymentTokenSaved,
                    $token
                );
                list($resultJson, $paymentTokenSaved) = $resultArr;
            } else {
                $paymentTokenSaved = 0;
                $this->messageManager->addError(self::FAILURE_MSG);
                $resultJson->setData(self::FAILURE);
                try {
                    $this->paymentChangeLogManager->validateFailedPaymentChanges($customerId);
                } catch (ValidationException $e) {
                    $this->messageManager->addError($e->getMessage());
                    $resultJson->setData($e->getMessage());
                }
                $this->paymentChangeLogManager->addRecord($customerId, true);

            }
        }
        $addId = $this->linkAddress->getAddressIdByPaymentId($paymentToken->getEntityId());
        if ($addId && $paymentTokenSaved) {
            $address = $this->addressRepository->getById($addId);
            $address = $this->setAddressData($address, $request);
            $this->addressRepository->save($address);
            $this->messageManager->addSuccessMessage(self::SUCCESS_MESSAGE);
            $resultJson->setData(self::SUCCESS);
            $this->paymentChangeLogManager->addRecord($customerId);
        } elseif (!$addId && $paymentTokenSaved) {
            $address = $this->addressDataFactory->create();
            $address = $this->setAddressData($address, $request);
            $this->addressRepository->save($address);
            $this->linkAddress->addLinkToAddressPayment($paymentToken->getEntityId(), $address->getId());
            $this->messageManager->addSuccessMessage(self::SUCCESS_MESSAGE);
            $resultJson->setData(self::SUCCESS);
            $this->paymentChangeLogManager->addRecord($customerId);
        }
        return $resultJson;
    }

    /**
     * Get Transaction Result
     *
     * @param $request
     * @return Error|Successful
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getTransactionResult($request): Error|Successful
    {
        $storeId = $this->session->getStoreId();
        $regionDetails = $this->regionFactory->create()->load($request[self::REGION_ID]);
        $customerSessionId = $this->customerSession->getSessionId();
        $btMerchantId = $this->getBtKountMerchantId();
        $amt = self::AMOUNT;
        if ($this->abbottSarpHelper->getTestUnsuccessfulTransaction()) {
            $amt = self::SANDBOX_UNSUCCESSFUL_AMOUNT;
        }

        $street = $request[self::STREET];
        return $this->adapterFactory->create($storeId)->sale(
            [
            'customer' => [
                'firstName' => $request[self::FIRSTNAME],
                'lastName' => $request[self::LASTNAME],
                'company' => $request[self::COMPANY],
                'phone' => $request[self::TELEPHONE],
                'email' => $this->customerSession->getCustomerData()->getEmail(),
            ],
            'amount' => $amt,
            'paymentMethodNonce' => $request["nonce"],
            'options' => [
            'submitForSettlement' => true,
            'storeInVaultOnSuccess' => true,
            'skipAdvancedFraudChecking' => true
            ],
            'billing' => [
              'firstName' => $request[self::FIRSTNAME],
              'lastName' => $request[self::LASTNAME],
              'company' => $request[self::COMPANY],
              'streetAddress' => $street[0],
              'locality' => $request['city'],
              'region' => $regionDetails['code'],
              'postalCode' => $request[self::POSTCODE],
              'countryCodeAlpha2' => $request[self::COUNTRY_ID]
                ],
            'deviceData' => '{"device_session_id":"'.$customerSessionId.'","fraud_merchant_id":"'.$btMerchantId.'"}',
            ]
        );
    }

    /**
     * Set Payment Token Data
     *
     * @param $paymentToken
     * @param $token
     * @param $transaction
     * @param $customerId
     * @return mixed
     * @throws Exception
     */
    private function setPaymentTokenData($paymentToken, $token, $transaction, $customerId)
    {
        $paymentToken->setGatewayToken($token);
        $paymentToken->setExpiresAt($this->getExpirationDate($transaction));
        $paymentToken->setTokenDetails(
            $this->convertDetailsToJSON(
                [
                'type' => $this->getCreditCardType($transaction->creditCardDetails->cardType),
                'maskedCC' => $transaction->creditCardDetails->last4,
                'expirationDate' => $transaction->creditCardDetails->expirationDate
                ]
            )
        );
        $paymentToken->setCustomerId($customerId);
        $paymentToken->setIsActive(true);
        $paymentToken->setIsVisible(true);
        $paymentToken->setPaymentMethodCode(self::CODE);
        $paymentToken->setPublicHash($this->generatePublicHash($paymentToken));
        return $paymentToken;
    }

    /**
     * Set Address Data
     *
     * @param $address
     * @param $request
     * @return mixed
     */
    private function setAddressData($address, $request): mixed
    {
        $address->setFirstname($request[self::FIRSTNAME])
        ->setLastname($request[self::LASTNAME])->setCompany($request[self::COMPANY])
            ->setCountryId($request[self::COUNTRY_ID])->setRegionId($request[self::REGION_ID])
            ->setCity($request['city'])->setPostcode($request[self::POSTCODE])
            ->setCustomerId($this->customerSession->getCustomer()->getId())
            ->setStreet($request[self::STREET])->setTelephone($request['telephone']);
        return $address;
    }

    /**
     * Convert Details to Json
     *
     * @param $details
     * @return bool|string
     */
    private function convertDetailsToJSON($details): bool|string
    {
        $json = $this->serializer->serialize($details);
        return $json ? $json : '{}';
    }

    /**
     * Generate Public Hash
     *
     * @param PaymentTokenInterface $paymentToken
     * @return string
     */
    protected function generatePublicHash(PaymentTokenInterface $paymentToken): string
    {
        $hashKey = $paymentToken->getGatewayToken();
        if ($paymentToken->getCustomerId()) {
            $hashKey = $paymentToken->getCustomerId();
        }
        $hashKey .= $paymentToken->getPaymentMethodCode()
            . $paymentToken->getType()
            . $paymentToken->getTokenDetails();
        return $this->encryptor->getHash($hashKey);
    }

    /**
     * Get Expiration Date
     *
     * @param Transaction $transaction
     * @return string
     * @throws Exception
     */
    private function getExpirationDate(Transaction $transaction): string
    {
        $expDate = new \DateTime(
            $transaction->creditCardDetails->expirationYear
            . '-'
            . $transaction->creditCardDetails->expirationMonth
            . '-'
            . '01'
            . ' '
            . '00:00:00',
            new \DateTimeZone('UTC')
        );
        $expDate->add(new \DateInterval('P1M'));
        return $expDate->format('Y-m-d 00:00:00');
    }

    /**
     * Get Credit Card Type
     *
     * @param $type
     * @return mixed
     * @throws NoSuchEntityException
     * @throws InputException
     */
    private function getCreditCardType($type): mixed
    {
        $replaced = str_replace(' ', '-', strtolower($type));
        $mapper = $this->config->getCcTypesMapper();
        return $mapper[$replaced];
    }

    /**
     * Check Added Card
     *
     * @param $resultJson
     * @param $customerId
     * @param $transaction
     * @param $token
     * @param $request
     * @return mixed
     */
    private function checkAddedCard($resultJson, $customerId, $transaction, $token, $request): mixed
    {
        try {
            $this->paymentChangeLogManager->validatePaymentChanges($customerId);
            $this->paymentChangeLogManager->validateFailedPaymentChanges($customerId);
            $paymentToken = $this->paymentTokenFactory
            ->create(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD);
            $this->setPaymentTokenData($paymentToken, $token, $transaction, $customerId);
            $this->isVisible = true;
            $existingId = $this->checkIfDuplicate($paymentToken);
            if ($existingId) {
                $paymentToken->setEntityId($existingId);
                $paymentToken->setIsVisible(true);
            }
            $this->paymentTokenRepository->save($paymentToken);
            $addId = $this->linkAddress->getAddressIdByPaymentId($paymentToken->getEntityId());
            $address = $addId ? $this->addressRepository->getById($addId) : $this->addressDataFactory->create();
            $address = $this->setAddressData($address, $request);
            $this->addressRepository->save($address);
            $this->linkAddress->addLinkToAddressPayment($paymentToken->getEntityId(), $address->getId());
            if ($existingId && $this->isVisible) {
                $this->messageManager->addSuccessMessage("Card already exists and replaced with new details");
                $resultJson->setData(self::DUPLICATE);
            } else {
                $this->messageManager->addSuccessMessage(self::SUCCESS_MESSAGE);
                $resultJson->setData(self::SUCCESS);
                $this->paymentChangeLogManager->addRecord($customerId);
            }
        } catch (ValidationException $e) {
            $this->messageManager->addError($e->getMessage());
            $resultJson->setData(self::FAILURE);
        } catch (AlreadyExistsException $e) {
            $this->logger->critical($e->getMessage());
            $this->messageManager->addError("Card already exists");
            $resultJson->setData(self::DUPLICATE);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->messageManager->addError("Error while saving the card");
            $resultJson->setData(self::FAILURE);
        }
        return $resultJson;
    }

    /**
     * Check Edited Card
     *
     * @param Json $resultJson
     * @param mixed $customerId
     * @param PaymentTokenInterface|null $paymentToken
     * @param mixed $transaction
     * @param int $paymentTokenSaved
     * @param string $token
     * @return array
     */
    private function checkEditedCard(
        $resultJson,
        $customerId,
        $paymentToken,
        $transaction,
        $paymentTokenSaved,
        $token
    ): array {
        try {
            $oldToken = clone $paymentToken;
            $this->setPaymentTokenData($paymentToken, $token, $transaction, $customerId);
            $existingId = $this->checkIfDuplicate($paymentToken);
            if ($existingId) {
                // if token already exists, we will hide old
                //token and assign entity id of existing token to $paymentToken object
                $oldToken->setIsVisible(false);
                $oldToken->setIsActive(false);
                $this->paymentTokenRepository->save($oldToken);
                // Get token entity id to avoid duplicate token hashes.
                $paymentToken->setEntityId($existingId);
            }
            $this->paymentChangeLogManager->validatePaymentChanges($customerId);
            $this->paymentChangeLogManager->validateFailedPaymentChanges($customerId);
            $this->paymentTokenRepository->save($paymentToken);
            $resultJson->setData(self::SUCCESS);
        } catch (ValidationException $e) {
            $paymentTokenSaved = 0;
            $this->messageManager->addError($e->getMessage());
            $resultJson->setData(self::FAILURE);
        } catch (AlreadyExistsException $e) {
            $this->logger->critical($e->getMessage());
            $paymentTokenSaved = 0;
            $this->messageManager->addError("Card already exists");
            $resultJson->setData(self::DUPLICATE);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            $paymentTokenSaved = 0;
            $this->messageManager->addError("Error while saving the card");
            $resultJson->setData(self::FAILURE);
        }
        return [$resultJson, $paymentTokenSaved];
    }
}
