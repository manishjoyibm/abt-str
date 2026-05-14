<?php

namespace Abbott\Subscriptionhistory\Helper;

use Abbott\Subscriptionhistory\Model\SubscriptionhistoryFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Psr\Log\LoggerInterface;
use Aheadworks\Sarp2\Model\Payment\Token;
use Aheadworks\Sarp2\Model\Profile\AddressFactory;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Abbott\GigyaIM\Helper\Data as GigyaHelper;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\DateTime\FormatConverter;
use \Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;

class Data extends AbstractHelper
{

    public $profileRepository;
    public $gigyaHelper;
    public $profileManagement;
    public $paymentTokenRepository;
    /**
     * @var SessionManagerInterface
     */
    protected $coreSession;

    /**
     * @var Subscriptionhistory
     */
    protected $subscriptionHistory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    const IS_IMPERSONATE = 0;

    const CHANGE_SUBSCIPTION_PLAN_EVENT = 'subscription_plan_change';

    /**
     * @var Token
     */
    protected $token;

    /**
     * @var AddressFactory
     */
    protected $profileAddress;

    /**
     * @var Session
     */
    protected $adminSession;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var PaymentsList
     */
    protected $paymentsList;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var FormatConverter
     */
    private $dateFormatConverter;

    const XML_PATH_ENABLE = 'aw_sarp2/MBO_subscription_history/enable';

    const SUBSCRIPTION_CREATE_PROFILE = 'create_subscription_profile';

    /**
     * Data constructor.
     * @param SessionManagerInterface $coreSession
     * @param SubscriptionhistoryFactory $subscriptionhistory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param LoggerInterface $logger
     * @param Token $token
     * @param AddressFactory $profileAddress
     * @param Session $adminSession
     * @param TimezoneInterface $timezone
     * @param PaymentsList $paymentsList
     * @param ProfileRepositoryInterface $profileRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        SessionManagerInterface $coreSession,
        SubscriptionhistoryFactory  $subscriptionhistory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        LoggerInterface $logger,
        Token $token,
        AddressFactory $profileAddress,
        Session $adminSession,
        TimezoneInterface $timezone,
        FormatConverter $dateFormatConverter,
        PaymentsList $paymentsList,
        ProfileRepositoryInterface $profileRepository,
        ScopeConfigInterface $scopeConfig,
        GigyaHelper $gigyaHelper,
        ProfileManagementInterface $profileManagement,
        PaymentTokenRepositoryInterface $paymentTokenRepository
    ) {
        $this->coreSession = $coreSession;
        $this->subscriptionHistory = $subscriptionhistory;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->token = $token;
        $this->profileAddress = $profileAddress;
        $this->adminSession = $adminSession;
        $this->timezone = $timezone;
        $this->paymentsList = $paymentsList;
        $this->profileRepository = $profileRepository;
        $this->scopeConfig = $scopeConfig;
        $this->gigyaHelper = $gigyaHelper;
        $this->profileManagement = $profileManagement;
        $this->dateFormatConverter = $dateFormatConverter;
        $this->paymentTokenRepository = $paymentTokenRepository;
    }

    public function prepareMBOSessionName()
    {

         return [
            'MBO_Card_change' => 'MBO_Card_change',
            'MBO_product_qty_change' => 'MBO_product_qty_change',
            'MBO_shipping_address_change' => 'MBO_shipping_address_change',
            'MBO_billing_address_change' => 'MBO_billing_address_change',
            'MBO_occurance_date_change' => 'MBO_occurance_date_change'
         ];
    }

    /**
     * @param $tokenId
     * @param $sessionName
     */
    public function getCreditCardDetailsBeforeSave($tokenId, $sessionName)
    {
        $ccData = [];
        $existingCard =  $this->paymentTokenRepository->get($tokenId);

        if ($existingCard->getType()) {
            $ccData['type'] = $existingCard->getType();
        }

        if ($existingCard->getPaymentMethod()) {
            $ccData['payment_method'] = $existingCard->getPaymentMethod();
        }

        if ($existingCard->getTokenValue()) {
            $ccData['gatewaytoken'] = $existingCard->getTokenValue();
        }

        if ($existingCard->getDetails('payerEmail')) {
            $ccData['paypal_email'] = $existingCard->getDetails('payerEmail');
        }

        if ($existingCard->getDetails('type')) {
            $ccData['card_type'] = $existingCard->getDetails('type');
        }

        if ($existingCard->getDetails('maskedCC')) {
            $ccData['maskedCC'] = $existingCard->getDetails('maskedCC');
        }

        $this->coreSession->setData($sessionName, $ccData);
    }

    /**
     * @param $sessionName
     * @param $profile
     * @param $newValue
     * @return mixed
     */
    public function compareCC($sessionName, $profile, $newValue)
    {

        $beforeCC = $this->coreSession->getData($sessionName);

        $cardDetails = $this->paymentTokenRepository->get($profile->getPaymentTokenId());

        if ((isset($beforeCC['gatewaytoken']) && !empty($beforeCC['gatewaytoken'])) &&
            (isset($cardDetails) && !empty($cardDetails->getTokenValue())) &&
            $beforeCC['gatewaytoken'] != $cardDetails->getTokenValue()) {
            $newValue['type'] = $cardDetails->getType();
            $newValue['maskedCC'] = $cardDetails->getDetails('maskedCC');
            $newValue['gatewaytoken'] = $cardDetails->getTokenValue();
            $newValue['payment_method'] = $cardDetails->getPaymentMethod();
            $newValue['paypal_email'] = $cardDetails->getDetails('payerEmail');
            $newValue['card_type']  = $cardDetails->getDetails('type');

            /**
             * Remove Gateway token from session
             */
            unset($beforeCC['gatewaytoken']);

            $this->coreSession->setData($sessionName, $beforeCC);
            return $newValue;
        }
    }

    /**
     * @param $beforeCC
     * @param $afterCC
     * @return array
     */
    public function getCCHistoryLog($beforeCC, $afterCC)
    {
        return [
           'beforeCCValue' => $this->jsonHelper->jsonEncode($beforeCC),
           'afterCCVlue' => $afterCC
        ];
    }


    /**
     * @param $profile
     * @param $eventName
     * @param $sessionNames
     * @param $newData
     * @return array
     */

    public function prepareData($profile, $eventName, $sessionNames, $newData, $imporsonate = null)
    {
        $imporsonate = ($imporsonate) ? $imporsonate :
            \Abbott\Subscriptionhistory\Helper\ChangeSubscriptionPlan::IS_IMPERSONATE;

        $data['profile_id'] = $profile->getProfileId();
        $data['subscription_id'] = $profile->getIncrementId();
        $data['customer_id'] = $profile->getCustomerId();
        $data['store_id'] = $profile->getStoreId();
        $data['is_impersonate'] = $imporsonate;
        $data['event_name'] = $eventName;
        $data['before_value'] = $this->jsonHelper->jsonEncode($this->getOldValues($sessionNames));
        $data['after_value'] = $this->jsonHelper->jsonEncode($newData);

        if (!is_null($this->adminSession->getUser())) {
            $data['mbo_user'] = $this->adminSession->getUser()->getUsername();
        }
        return $data;
    }


    /**
     * @param $sessionNames
     * @return array
     */
    public function getOldValues($sessionNames)
    {

        $oldValues = [];
        foreach ($sessionNames as $key => $value) {
            $oldValues[$key] = $this->coreSession->getData($value);
            $this->coreSession->unset($key);
        }

        return $oldValues;
    }


    /**
     * @param $profile
     * @param $eventName
     * @param $sessionNames
     * @param $newData
     */
    public function saveSubscriptionHistoryLog($profile, $eventName, $sessionNames, $newData)
    {
        $subscriptionLog = $this->subscriptionHistory->create();
        if (!empty($newData)) {
            $data = $this->prepareData($profile, $eventName, $sessionNames, $newData);
        }
        try {
             $subscriptionLog->setData($data)->save();
             $this->destroySession($sessionNames);
             return $subscriptionLog;
        } catch (\Exception $e) {
             $this->logger->critical($e->getMessage());
        }
    }

    /**
     * @param $sessionNames
     */
    public function destroySession($sessionNames)
    {
        foreach ($sessionNames as $key => $value) {
            $this->coreSession->unsetData($key);
        }
    }

    /**
     * @param $profileItem
     * @param $profile
     * @return mixed
     */
    public function getProfileItem($profileItem, $profile)
    {
        return $profileItem->create()
                          ->getCollection()
                          ->addFieldToFilter('profile_id', $profile->getProfileId());
    }

    /**
     * @param $sessionName
     * @param $profile
     * @param $profileItem
     */
    public function getProductQtyBeforeSave($sessionName, $profile, $profileItem)
    {
        $products = [];

        $oldItems = $this->getProfileItem($profileItem, $profile);

        if ($oldItems->getSize() > 0) {
            foreach ($oldItems as $item) {
                $products[$item->getSku()] = (int)$item->getQty();
            }
        }

        $this->setProductQtyInSession($products, $sessionName);
    }

    /**
     * @param $products
     * @param $sessionName
     */
    public function setProductQtyInSession($products, $sessionName)
    {
        $this->coreSession->setData($sessionName, $products);
    }

    /**
     * @param $sessionName
     * @param $profile
     * @param $newValue
     * @param $profileItem
     * @return void
     */
    public function compareProductQty($sessionName, $profile, $newValue, $profileItem)
    {
        $items = $this->getProfileItem($profileItem, $profile);
        $i = 0;
        $oldValue = $this->coreSession->getData($sessionName);
        if ($items->getSize() > 0) {
            foreach ($items as $item) {

                if ($oldValue[$item->getSku()] != (int)$item->getQty()) {
                    $newValue[$item->getSku()] = (int)$item->getQty();
                } else {
                    unset($oldValue[$item->getSku()]);
                    $i++;
                }
            }
        }

        if ($i == $items->getSize()) {
            $this->coreSession->unsetData($sessionName);
            return ;
        }
        $this->coreSession->setData($sessionName, $oldValue);
        return $newValue;
    }

    /**
     * @param $profileAddress
     * @param $sessionName
     */
    public function getProfileShippingAddressBeforeValue($profileAddress, $sessionName)
    {
        $oldProfileAddress = $this->getAddressData($profileAddress);

        $this->coreSession->setData($sessionName, $oldProfileAddress);
    }

    /**
     * @param $profileAddress
     * @return array
     */
    public function getAddressData($profileAddress)
    {
        $data = [];
        $data['address_id'] = $profileAddress->getAddressId();
        $data['street'] = $profileAddress->getStreet();
        $data['city'] = $profileAddress->getCity();
        $data['region'] = $profileAddress->getRegion();
        $data['country'] = $profileAddress->getCountryId();
        $data['postcode'] = $profileAddress->getPostcode();
        return $data;
    }

    /**
     * @param $profile
     * @param $profileAddress
     * @param $type
     * @param $sessionName
     * @return array
     */
    public function compareProfileShippingAddress($profile, $profileAddress, $type, $sessionName)
    {
        $address = $this->profileAddress->create()
                                        ->getCollection()
                                        ->addFieldToFilter('profile_id', $profile->getProfileId())
                                        ->addFieldToFilter('address_type', $type)
                                        ->getFirstItem();

        $oldAddress = $this->coreSession->getData($sessionName);
        if (isset($oldAddress['address_id']) && !empty($oldAddress['address_id'])) {
            if (($oldAddress['street'] != $address->getStreet()) ||
                ($oldAddress['city'] != $address->getCity()) ||
                ($oldAddress['region'] != $address->getRegion()) ||
                ($oldAddress['postcode'] != $address->getPostcode())) {
                return $this->getAddressData($address);
            } else {
                $this->coreSession->unsetData($sessionName);
            }
        } else {
            $this->coreSession->unsetData($sessionName);
        }
    }

    /**
     * @param $profile
     * @param $profileAddress
     * @param $type
     * @param $sessionName
     * @param $newData
     * @return mixed
     */
    public function compareProfileAddress($profile, $profileAddress, $type, $sessionName, $newData)
    {
        foreach ($type as $addressType) {
            $newData[$sessionName[$addressType]] = $this->compareProfileShippingAddress(
                $profile,
                $profileAddress,
                $addressType,
                $sessionName[$addressType]
            );
        }
         return $newData;
    }

    /**
     * @param $sessionName
     * @param $data
     */
    public function getOccuranceDateBeforeValue($sessionName, $payment)
    {
        $occuranceDate = $this->coreSession->getData($sessionName);
        $data[$payment->getItemId()] = $payment->getScheduledAt();
        if ($occuranceDate == null) {
            $occuranceDate = [];
        }

        $this->coreSession->setData($sessionName, $data);
    }

    /**
     * @param $profileId
     * @return \Aheadworks\Sarp2\Engine\Payment[]
     */
    public function getProfileScheduledDate($profileId)
    {
        return $this->paymentsList->getLastScheduled($profileId);
    }

    /**
     * @param $sessionName
     * @param $profile
     * @return array
     * @throws \Exception
     */
    public function compareNextOccuranceDate($sessionName, $profile)
    {
        $data = [];
        $occuranceDate = $this->coreSession->getData($sessionName);
        $nextOccuranceDate = $this->getProfileScheduledDate($profile->getProfileId());
        if ($occuranceDate != null) {
            foreach ($occuranceDate as $key => $OldOccuranceDate) {
                $oldDate = $this->timezone->date(new \DateTime($OldOccuranceDate))->format('Y-m-d');
                if ($OldOccuranceDate != $nextOccuranceDate[$key]) {
                    $newDate = $this->timezone->date(
                        new \DateTime($nextOccuranceDate[$key]->getScheduledAt())
                    )->format('Y-m-d');
                    if (strtotime($oldDate) != strtotime($newDate)) {
                        $data[$key] = $nextOccuranceDate[$key]->getScheduledAt();
                        return $data;
                    } else {
                        $this->coreSession->unsetData($sessionName);
                    }
                } else {
                    $this->coreSession->unsetData($sessionName);
                }
            }
        } else {
            $this->coreSession->unsetData($sessionName);
        }
    }

    /**
     * @param $profileId
     * @param $oldDataSessionKey
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeChangeSubscriptionPlanLog($profileId, $oldDataSessionKey)
    {
        $profile = $this->profileRepository->get($profileId);
        $oldPlan = [
                       'plan_id' => $profile->getPlanId(),
                       'plan_name' => $profile->getPlanName()
                   ];
        $this->coreSession->setData($oldDataSessionKey, $oldPlan);
    }

    /**
     * Compare Plan Value and Save for Subscription
     * @param $profile
     *
     */
    public function comparePlanvalueAndSave($profile, $eventName, $sessionNames)
    {
        $oldPlan = $this->coreSession->getData(key($sessionNames));
        $newData[key($sessionNames)] = [
            'plan_id' => $profile->getPlanId(),
            'plan_name' => $profile->getPlanName()
        ];

        if ($oldPlan['plan_id'] != $newData[key($sessionNames)]['plan_id']) {
            try {
                $this->saveSubscriptionHistoryLog($profile, $eventName, $sessionNames, $newData);

            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }

    /**
     * @param $profile
     * @param $sessionName
     */
    public function getProfileStateBeforeData($profile, $sessionName)
    {
        $data['status'] = $profile->getStatus();
        $this->coreSession->setData($sessionName, $data);
    }

    /**
     * @param $sessionName
     * @param $newProfile
     * @return array
     */
    public function compareProfileStatus($sessionName, $newProfile)
    {
        $oldStatus = $this->coreSession->getData($sessionName);
        $newStatus = $newProfile->getStatus();
        $data = [];
        if ($oldStatus != $newStatus) {
            $data['status'] = $newStatus;
            return $data;
        } else {
            $this->destroySession($sessionName);
        }
    }

    /**
     * @param $profileId
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProfile($profileId)
    {
        try {
            return $this->profileRepository->get($profileId);
        } catch (\Exception $e) {
            $this->logger->log($e->getMessage());
        }
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getSubscriptionHistoryStatus($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * get next payment info
     * @param $profileId
     * @return mixed
     */
    public function getNextPaymentInfo($profileId)
    {
        $nextPaymentInfo = $this->profileManagement->getNextPaymentInfo($profileId);
        $nextPaymentDate = $nextPaymentInfo->getPaymentDate();
        if ($nextPaymentDate) {
            $nextPaymentDate = new \DateTime($nextPaymentDate);
            $nextPaymentDateAsDateTime = $this->timezone->date($nextPaymentDate);
            $nextPaymentDate = $nextPaymentDateAsDateTime->format(
                DateTime::DATETIME_PHP_FORMAT
            );
        }

        return $nextPaymentDate;
    }

    /**
     * Prepare frotend data and save the log
     * @param $profileId
     * @return mixed
     */
    public function prepareFrontendData($profile, $eventName, $oldData, $newData, $imporsonate = self::IS_IMPERSONATE)
    {
        $mageUserLoggedIn = $this->gigyaHelper->getCustomCookie('mage_usr_imp');
        if ($mageUserLoggedIn!= "" && !is_null($this->adminSession->getUser())) {
            $data['mbo_user'] = $this->adminSession->getUser()->getUsername();
        }
        if ($mageUserLoggedIn!= "") {
            $imporsonate = 1;
        }
        $data['profile_id'] = $profile->getProfileId();
        $data['subscription_id'] = $profile->getIncrementId();
        $data['customer_id'] = $profile->getCustomerId();
        $data['store_id'] = $profile->getStoreId();
        $data['is_impersonate'] = $imporsonate;
        $data['event_name'] = $eventName;
        $data['before_value'] = $this->jsonHelper->jsonEncode($oldData);
        $data['after_value'] = $this->jsonHelper->jsonEncode($newData);

        if (!empty($data)) {
            $subscriptionLog = $this->subscriptionHistory->create();
            try {
                 $subscriptionLog->setData($data)->save();
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }

    /**
     * Prepare profile data while profile crete
     * @param $profileId
     * @return mixed
     */
    public function setProfileHistoryData($profileId)
    {
        $profileData = [];
        if ($profileId) {
            $profile = $this->getProfile($profileId);
            $items = $profile->getItems();
            foreach ($items as $item):
                $data[] = [$item->getSku() => $item->getName(), 'qty' => $item->getQty()];
            endforeach;
            $profileData[self::SUBSCRIPTION_CREATE_PROFILE] = $data;
            if (!empty($profileData[self::SUBSCRIPTION_CREATE_PROFILE])) {
                $this->prepareFrontendData($profile, self::SUBSCRIPTION_CREATE_PROFILE, $profileData, $profileData);
            }
        }
    }
}
