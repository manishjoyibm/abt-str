<?php

namespace Abbott\Sarp2\Controller\Subscription;

use \Aheadworks\Sarp2\Api\Data\ProfileInterface;
use \Magento\Framework\Exception\LocalizedException;
use Abbott\Subscriptionhistory\Helper\Data as HistoryDataLog;

class ChangeVault extends \Magento\Framework\App\Action\Action
{

    public $historyDataLog;
    const CHANGE_SUBSCIPTION_PAYMENT_EVENT = "subscription_payment_method_change";
    
    protected $profileRepository;

    protected $paymentTokenInterface;

    protected $logger;

    protected $paymentTokenFactory;

    protected $paymentTokenRepository;

    protected $json;

    protected $messageManager;

    protected $paymentsList;

    private $paymentPersistence;

    protected $customerSession;

    protected $searchCriteriaBuilder;

    protected $formKeyValidator;

    protected $addressPaymentLink;

    const BILLING_ADDRESS = "billing";

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Aheadworks\Sarp2\Api\ProfileRepositoryInterface $profileRepository,
        \Magento\Vault\Api\PaymentTokenRepositoryInterface $paymentTokenInterface,
        \Psr\Log\LoggerInterface $logger,
        \Aheadworks\Sarp2\Api\Data\PaymentTokenInterfaceFactory $paymentTokenFactory,
        \Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface $paymentTokenRepository,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Aheadworks\Sarp2\Engine\Payment\PaymentsList $paymentsList,
        \Aheadworks\Sarp2\Engine\Payment\Persistence $paymentPersistence,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Abbott\CreditCards\Model\AddressPaymentTokenLink $addressPaymentLink,
		HistoryDataLog $historyDataLog
    ) {
        $this->profileRepository = $profileRepository;
        $this->paymentTokenInterface = $paymentTokenInterface;
        $this->logger = $logger;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->json = $json;
        $this->messageManager = $messageManager;
        $this->paymentsList = $paymentsList;
        $this->paymentPersistence = $paymentPersistence;
        $this->customerSession = $customerSession;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->formKeyValidator = $formKeyValidator;
        $this->addressPaymentLink = $addressPaymentLink;
        $this->historyDataLog = $historyDataLog;
        parent::__construct($context);
    }

    public function execute()
    {
        $post = $this->getRequest()->getParams();
        $profileId = isset($post['profile_id']) ? $post['profile_id'] : null;
        $vaultId = isset($post['payment']['vaultId']) ? $post['payment']['vaultId'] : null;
        try {
            if (!$this->customerSession->create()->isLoggedIn()) {
                throw new LocalizedException(__('Invalid customer, Please login and try again.'));
            }
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                throw new LocalizedException(__('Invalid Form Key. Please refresh the page.'));
            }
            $customerId = $this->customerSession->create()->getCustomer()->getId();
            $validProfile = $this->checkCustomerProfile($customerId, $profileId);
            if ($profileId && $vaultId && $validProfile) {
                $profile = $this->profileRepository->get($profileId);
                $oldTokenId = $profile->getPaymentTokenId();
                $tokenData = $this->paymentTokenInterface->getById($vaultId);
                if ($tokenData) {
                    $awSarp2TokenId = $this->saveAwSarp2Token($tokenData);
                    if ($awSarp2TokenId > 0) {
                        $profile->setPaymentTokenId($awSarp2TokenId);
                        $profile->setPaymentMethod($tokenData->getPaymentMethodCode());
                        $this->profileRepository->save($profile);
                        $newPaymentTokenId = $profile->getPaymentTokenId();
                        $payments = $this->paymentsList->getLastScheduled($profileId);
                        foreach ($payments as $payment) {
                            $paymentData = $payment->getPaymentData();
                            $paymentData['token_id'] = $profile->getPaymentTokenId();
                            $payment->setPaymentData($paymentData);
                        }
                        if (is_array($payments) && count($payments)) {
                            $this->paymentPersistence->massSave($payments);
                        }
                        
                        //Updating the profile billing address
                        $addressId = $this->addressPaymentLink->getAddressIdByPaymentId($vaultId);
                        $address = $this->getAddressById($this->customerSession->create()->getCustomer(), $addressId);
                        if ($address) {
                            foreach ($profile->getAddresses() as $profileAddressData) {
                                if ($profileAddressData->getAddressType() == self::BILLING_ADDRESS) {
                                    $profileAddressData->setStreet($address->getStreet()[0]);
                                    $profileAddressData->setCity($address->getCity());
                                    $profileAddressData->setRegion($address->getRegion());
                                    $profileAddressData->setRegionId($address->getRegionId());
                                    $profileAddressData->setPostcode($address->getPostcode());
                                    $profileAddressData->setCustomerAddressId($addressId);
                                    $profileAddressData->setUpdatedAt(date("Y-m-d"));
                                    $profileAddressData->save();
                                }
                            }
                        }
                        
                        //add logs for payment method change
						if($newPaymentTokenId != $oldTokenId && $this->historyDataLog->getSubscriptionHistoryStatus($profile->getStoreId())){
							$existingCard = $this->paymentTokenRepository->get($oldTokenId);
							$newCard = $this->paymentTokenRepository->get($newPaymentTokenId);
							$oldData[self::CHANGE_SUBSCIPTION_PAYMENT_EVENT] =['type' => $existingCard->getType(), 'payment_method' => $existingCard->getPaymentMethod(), 'gateway_token' => $existingCard->getTokenValue(), 'paypal_email' => $existingCard->getDetails('payerEmail'), 'card_type' => $existingCard->getDetails('type'), 'maskedCC' => $existingCard->getDetails('maskedCC')];
							$newData[self::CHANGE_SUBSCIPTION_PAYMENT_EVENT] =['type' => $newCard->getType(),  'payment_method' => $newCard->getPaymentMethod(), 'gateway_token' => $newCard->getTokenValue(), 'paypal_email' => $newCard->getDetails('payerEmail'),  'card_type' => $newCard->getDetails('type'), 'maskedCC' => $newCard->getDetails('maskedCC')];
							$this->historyDataLog->prepareFrontendData($profile, self::CHANGE_SUBSCIPTION_PAYMENT_EVENT, $oldData, $newData);		
		
						}

                        $this->messageManager->addSuccess(__("Payment details has been updated successfully."));
                    }
                } else {
                    $this->messageManager->addError(__("Credit card details not found in system."));
                }
            } else {
                $this->messageManager->addError(__("Profile data not found."));
            }
        } catch (\Exception $e) {
            $this->logger->error("Error Change Vault: " . $e->getMessage());
            $this->messageManager->addError(__("An error occrred while updating the payment details, please try again."));
        }

        $this->_redirect('aw_sarp2/profile_edit/index/', ["profile_id" => $profileId]);
    }

    /**
     * Saving token value in aw_sarp2_payment_token table
     *
     * @param $paymentData
     * @return integer
     */
    public function saveAwSarp2Token($paymentData)
    {
        try {
            $details = $this->json->unserialize($paymentData->getDetails());
            $type = isset($details['type']) ? $details['type'] : null;
            $maskedCC = isset($details['maskedCC']) ? $details['maskedCC'] : null;
            $expirationDate = isset($details['expirationDate']) ? $details['expirationDate'] : null;
            $tokenType = $paymentData->getType();
            $paymentMethod = $paymentData->getPaymentMethodCode();
            $paymentToken = $this->paymentTokenFactory->create();
            $paymentToken->setPaymentMethod($paymentMethod)
                ->setType($tokenType)
                ->setTokenValue($paymentData->getGatewayToken())
                ->setIsActive(true);
            $paymentToken->setExpiresAt($paymentData->getExpiresAt())->setDetails(
                'type',
                $type
            )->setDetails(
                'maskedCC',
                $maskedCC
            )->setDetails(
                'expirationDate',
                $expirationDate
            );
            $this->paymentTokenRepository->save($paymentToken);
            return $paymentToken->getId();
        } catch (\Exception $e) {
            $this->logger->error("Error Change Vault: " . $e->getMessage());
        }
    }

    /**
     * Check Customer Profiles
     *
     * @param int  $customerId
     * @return integer
     */
    public function checkCustomerProfile($customerId, $profileId)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(ProfileInterface::CUSTOMER_ID, $customerId, 'in')->addFilter(ProfileInterface::PROFILE_ID, $profileId, 'in')->create();
        $profiles = $this->profileRepository->getList($searchCriteria)->getItems();
        return count($profiles);

    }

    /** get Customer Address
     * @param int  $customerId, $addressId
     * @return $address
     */

    public function getAddressById($customer, $addressId)
    {

        foreach ($customer->getAddresses() as $address) {
            if ($address->getId() == $addressId) {
                return $address;
            }
        }
        return null;
    }

}
