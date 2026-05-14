<?php


namespace Abbott\Subscriptionhistory\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Abbott\Subscriptionhistory\Model\SubscriptionhistoryFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Payment\Model\CcConfig;
use Magento\Payment\Helper\Data as PaymentHelper;
use Abbott\GigyaIM\Helper\Data as GigyaHelper;

class HistoryMessages extends AbstractHelper
{

    public $historyFactory;
    public $productRepository;
    public $ccConfig;
    public $paymentHelper;
    public $gigyaHelper;
    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var
     */
    protected $message;

    /**
     * @var TimezoneInterface
     */
    protected $timezoneInterface;

    public const MBO_PRODUCT_QTY_CHANGE = 'MBO_product_qty_change';
    public const MBO_OCCURANCE_DATE_CHANGE = 'MBO_occurance_date_change';
    public const MBO_SHIPPING_ADDRESS_CHANGE = 'MBO_shipping_address_change';
    public const MBO_BILLING_ADDRESS_CHANGE = 'MBO_billing_address_change';
    public const MBO_CARD_CHANGE = 'MBO_Card_change';
    public const MBO_PROFILE_PAUSE = 'MBO_PROFILE_PAUSE';
    public const MBO_PROFILE_RESUME = 'MBO_PROFILE_RESUME';
    public const MBO_PROFILE_CANCELLED = 'MBO_PROFILE_CANCELLED';
    public const MBO_PROFILE_PLAN_CHANGE = 'MBO_profile_plan_change';
    public const CRON_PROFILE_CANCELLED = 'CRON_PROFILE_CANCELLED';

    //constant for frontend events
    public const PRODUCT_QTY_CHANGE = 'subscription_profile_qty_change';
    public const OCCURANCE_DATE_CHANGE = 'subscription_payment_date_change';
    public const SHIPPING_ADDRESS_CHANGE = 'subscription_shipping_address_change';
    public const PAYMENT_METHOD_CHANGE = 'subscription_payment_method_change';
    public const PROFILE_CANCELLED_STATUS = 'subscription_profile_cancel';
    public const PROFILE_PLAN_CHANGE = 'subscription_plan_change';
    public const PROFILE_REMOVE_PRODUCT = 'subscription_profile_remove_product';
    public const PROFILE_CHANGE_PRODUCT = 'subscription_profile_change_product';
    public const SUBSCRIPTION_CREATE_PROFILE = 'create_subscription_profile';

    public const XML_CHANGE_PRODUCT_QTY = 'aw_sarp2/MBO_subscription_history/change_product_qty';
    public const XML_CHANGE_OCCURANCE_DATE = 'aw_sarp2/MBO_subscription_history/change_occurance_date';
    public const XML_CHANGE_SHIPPING_ADDRESS = 'aw_sarp2/MBO_subscription_history/change_shipping_address';
    public const XML_CHANGE_BILLING_ADDRESS = 'aw_sarp2/MBO_subscription_history/change_billing_address';
    public const XML_CHANGE_CARD_DATA = 'aw_sarp2/MBO_subscription_history/change_card_data';
    public const XML_CHANGE_PROFILE_STATUS_TO_PAUSE =
        'aw_sarp2/MBO_subscription_history/change_profile_to_pause_status';
    public const XML_CHANGE_PROFILE_STATUS_TO_RESUME =
        'aw_sarp2/MBO_subscription_history/change_profile_to_resume_status';
    public const XML_CHANGE_PROFILE_STATUS_TO_CANCELLED =
        'aw_sarp2/MBO_subscription_history/change_profile_to_cancelled_status';
    const XML_CHANGE_PROFILE_PLAN_CHANGE = 'aw_sarp2/MBO_subscription_history/change_profile_plan';
    const XML_PROFILE_REMOVE_PRODUCT = 'aw_sarp2/MBO_subscription_history/profile_remove_product';
    const XML_PROFILE_CHANGE_PRODUCT = 'aw_sarp2/MBO_subscription_history/profile_change_product';
    const XML_CHANGE_PROFILE_STATUS_TO_CANCELLED_BY_CRON =
        'aw_sarp2/MBO_subscription_history/change_profile_to_cancelled_status_by_cron';
    const XML_PATH_ENABLE = 'aw_sarp2/MBO_subscription_history/enable';
    const XML_SUBSCRIPTION_CREATE_PROFILE = 'aw_sarp2/MBO_subscription_history/profile_create';
    const MESSAGE = ' by CR Agent';
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * HistoryMessages constructor.
     * @param Data $jsonHelper
     * @param TimezoneInterface $timezoneInterface
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Data $jsonHelper,
        TimezoneInterface $timezoneInterface,
        ScopeConfigInterface $scopeConfig,
        SubscriptionhistoryFactory $historyFactory,
        ProductRepository $productRepository,
        CcConfig $ccConfig,
        PaymentHelper $paymentHelper,
        GigyaHelper $gigyaHelper
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->timezoneInterface = $timezoneInterface;
        $this->scopeConfig = $scopeConfig;
        $this->historyFactory = $historyFactory;
        $this->productRepository = $productRepository;
        $this->ccConfig = $ccConfig;
        $this->paymentHelper = $paymentHelper;
        $this->gigyaHelper = $gigyaHelper;
    }

    /**
     * @param $date
     * @return string
     * @throws \Exception
     */
    public function getDateByTimeZone($date)
    {
        return $this->timezoneInterface
            ->date(new \DateTime($date))
            ->format('Y-m-d H:i:s');
    }

    /**
     * @param $sku
     */
    public function getProduct($sku)
    {
    }

    /**
     * @param $row
     * @return null
     */
    public function getDataChanged($row, $isFrontend = null)
    {
        $oldValue = $this->jsonHelper->jsonDecode($row->getBeforeValue());
        $newValue = $this->jsonHelper->jsonDecode($row->getAfterValue());
        $this->message = null;
        foreach ($newValue as $event => $value) {
            switch ($event) {
                case self::MBO_PRODUCT_QTY_CHANGE:
                case self::PRODUCT_QTY_CHANGE:
                    $this->getQtyChangeMessage($oldValue[$event], $newValue[$event], $row, '', $isFrontend);
                    break;

                case self::MBO_OCCURANCE_DATE_CHANGE:
                case self::OCCURANCE_DATE_CHANGE:
                    $this->getOccuranceDateChangeMessage($oldValue[$event], $newValue[$event], $row, $isFrontend);
                    break;

                case self::MBO_SHIPPING_ADDRESS_CHANGE:
                case self::MBO_BILLING_ADDRESS_CHANGE:
                case self::SHIPPING_ADDRESS_CHANGE:
                    $this->getAddressChangeMessage($oldValue[$event], $newValue[$event], $row, $event, $isFrontend);
                    break;

                case self::MBO_CARD_CHANGE:
                case self::PAYMENT_METHOD_CHANGE:
                    $this->getCardDataChangeMessage($oldValue[$event], $newValue[$event], $row, $isFrontend);
                    break;

                case self::MBO_PROFILE_PAUSE:
                case self::MBO_PROFILE_RESUME:
                case self::MBO_PROFILE_CANCELLED:
                case self::CRON_PROFILE_CANCELLED:
                case self::PROFILE_CANCELLED_STATUS:
                    $this->getProfileStatusChangeMessage($row, $event, $isFrontend);
                    break;

                case self::MBO_PROFILE_PLAN_CHANGE:
                case self::PROFILE_PLAN_CHANGE:
                    $this->getProfilePlanChangeMessage($oldValue[$event], $newValue[$event], $row, $isFrontend);
                    break;
                case self::PROFILE_REMOVE_PRODUCT:
                    $this->getProfileProductRemoveMessage($oldValue[$event], $newValue[$event], $row, $isFrontend);
                    break;
                case self::PROFILE_CHANGE_PRODUCT:
                    $this->getProfileChangeProductMessage($oldValue[$event], $newValue[$event], $row, $isFrontend);
                    break;
                case self::SUBSCRIPTION_CREATE_PROFILE:
                    $this->getProfileCreateMessage($oldValue[$event], $newValue[$event], $row, $isFrontend);
                    break;
            }
        }

        return $this->message;
    }

    /**
     * @param $oldValue
     * @param $newValue
     * @param $createdAt
     * @return string
     */
    public function getQtyChangeMessage($oldValue, $newValue, $row = null, $storeId = '', $isFrontend = '', $isImp = '')
    {
        $storeId = ($storeId) ? $storeId : $row->getStoreId();
        $message = $this->getConfigMessage(self::XML_CHANGE_PRODUCT_QTY, $storeId);
        if (!empty($row)) {
            $imporsonateMsg =  '';

            if ($row->getIsImpersonate() || (!empty($row->getMboUser() && !is_null($row->getMboUser())))) {
                $imporsonateMsg = self::MESSAGE;
            }
            $createdAt = $this->getDateByTimeZone($row->getCreatedAt());
        } else {
            $createdAt = $this->getDateByTimeZone(date("Y-m-d H:i:s"));
            $mageUserLoggedIn = $this->gigyaHelper->getCustomCookie('mage_usr_imp');
            $imporsonateMsg = ($mageUserLoggedIn || $isImp!='') ? self::MESSAGE: '';
        }

        foreach ($newValue as $sku => $qty) {
            $newProduct =  $this->productRepository->get($sku);
            $variables = [
                '{created_at}' => $createdAt,
                '{name}' => $newProduct->getName(),
                '{product}' => $sku,
                '{oldQty}' => number_format($oldValue[$sku]),
                '{newQty}' => number_format($qty),
                '{crAgent}' => $imporsonateMsg
            ];
            if ($isFrontend!="") {
                $this->message .= '<tr><td>'.strtr($message, $variables).'<br></td></tr>';
            } else {
                $this->message .= strtr($message, $variables).'<br>';
            }
        }

        return $this->message;
    }

    /**
     * @param $oldValue
     * @param $newValue
     * @param $createdAt
     * @return string
     * @throws \Exception
     */
    public function getOccuranceDateChangeMessage($oldValue, $newValue, $row, $isFrontend = '')
    {
        $message = $this->getConfigMessage(self::XML_CHANGE_OCCURANCE_DATE, $row->getStoreId());
        $imporsonateMsg = '';

        if ($row->getIsImpersonate() || (!empty($row->getMboUser() && !is_null($row->getMboUser())))) {
            $imporsonateMsg = self::MESSAGE;
        }

        foreach ($newValue as $key => $date) {
            $variables = [
                '{created_at}' => $this->getDateByTimeZone($row->getCreatedAt()),
                '{oldDate}' => $this->timezoneInterface->date(new \DateTime($oldValue[$key]))->format('d M, Y'),
                '{newDate}' => $this->timezoneInterface->date(new \DateTime($date))->format('d M, Y'),
                '{crAgent}' => $imporsonateMsg
            ];
            if ($isFrontend!="") {
                $this->message .= '<tr><td>'.strtr($message, $variables).'<br></td></tr>';
            } else {
                $this->message .= strtr($message, $variables).'<br>';
            }
        }

        return $this->message;
    }

    /**
     * @param $oldValue
     * @param $newValue
     * @param $createdAt
     * @param $event
     * @throws \Exception
     */
    public function getAddressChangeMessage($oldValue, $newValue, $row, $event, $isFrontend = '')
    {
            $imporsonateMsg =  '';

        if ($row->getIsImpersonate() || (!empty($row->getMboUser() && !is_null($row->getMboUser())))) {
            $imporsonateMsg = self::MESSAGE;
        }

        if ($event == self::MBO_SHIPPING_ADDRESS_CHANGE || $event == self::SHIPPING_ADDRESS_CHANGE) {
            $message = $this->getConfigMessage(self::XML_CHANGE_SHIPPING_ADDRESS, $row->getStoreId());
        }

        if ($event == self::MBO_BILLING_ADDRESS_CHANGE) {
            $message = $this->getConfigMessage(self::XML_CHANGE_BILLING_ADDRESS, $row->getStoreId());
        }
            $variables = [
                '{created_at}' => $this->getDateByTimeZone($row->getCreatedAt()),
                '{oldAddress}' => $this->getAddressInSingleLine($oldValue),
                '{newAddress}' => $this->getAddressInSingleLine($newValue),
                '{crAgent}' => $imporsonateMsg
            ];
            if ($isFrontend!="") {
                $this->message .= '<tr><td>'.strtr($message, $variables).'<br></td></tr>';
            } else {
                $this->message .= strtr($message, $variables).'<br>';
            }
    }

    /**
     * @param $address
     * @return string
     */
    public function getAddressInSingleLine($address)
    {
        $variable = '';
        if (!empty($address['street'])) {
            if (is_array($address['street']) &&
                count($address['street']) > 1 &&
                array_key_exists('0', $address['street'])
            ) {
                $street1 = (array_key_exists('0', $address['street'])) ? $address['street']['0'] .'' : '';
                $street2 = (array_key_exists('1', $address['street'])) ? $address['street']['1'] .'' : '';
                $variable = $street1.' '.$street2.', ';
            } else {
                $variable = $address['street'].', ';
            }
        }
        if (!empty($address['city'])) {
            $variable .= $address['city'].', ';
        }

        if (!empty($address['region'])) {
            $variable .= $address['region'].', ';
        }

        if (!empty($address['postcode'])) {
            $variable .= $address['postcode'];
        }
        return $variable;
    }

    /**
     * @param $oldValue
     * @param $newValue
     * @param $createdAt
     * @throws \Exception
     */
    public function getCardDataChangeMessage($oldValue, $newValue, $row, $isFrontend = '')
    {
        $message = $this->getConfigMessage(self::XML_CHANGE_CARD_DATA, $row->getStoreId());
        $imporsonateMsg =  '';
        if ($row->getIsImpersonate() || (!empty($row->getMboUser() && !is_null($row->getMboUser())))) {
            $imporsonateMsg = self::MESSAGE;
        }
        $oldMaskedCc = '';
        $newMaskedCc = '';
        $ccTypes = $this->ccConfig->getCcAvailableTypes();
        $methods = $this->paymentHelper->getPaymentMethodList();
        $oldPayment = explode("_", $oldValue['payment_method']);
        if (count($oldPayment) > 1) {
            $oldMethod = $methods[$oldValue['payment_method']];
        } else {
            $oldMethodCc = $oldValue['card_type'];
            $oldMethod = $ccTypes[$oldMethodCc];
        }

        $newPayment = explode("_", $newValue['payment_method']);
        if (count($newPayment) > 1) {
               $newMethod = $methods[$newValue['payment_method']];
        } else {
            $newMethodCc = $newValue['card_type'];
            $newMethod = $ccTypes[$newMethodCc];
        }
        if (array_key_exists('maskedCC', $oldValue) && isset($oldValue['maskedCC'])) {
            $oldMaskedCc = ' '.$oldValue['maskedCC'];
        }
        if (array_key_exists('paypal_email', $oldValue) && isset($oldValue['paypal_email'])) {
            $oldMaskedCc = ' &lt;'.$oldValue['paypal_email'].'&gt;';
        }

        if (array_key_exists('maskedCC', $newValue) && isset($newValue['maskedCC'])) {
            $newMaskedCc = ' '.$newValue['maskedCC'];
        }
        if (array_key_exists('paypal_email', $newValue) && isset($newValue['paypal_email'])) {
            $newMaskedCc = ' &lt;'.$newValue['paypal_email'].'&gt;';
        }

        $variables = [
            '{created_at}' => $this->getDateByTimeZone($row->getCreatedAt()),
            '{oldCard}' => $oldMethod.$oldMaskedCc,
            '{newCard}' => $newMethod.$newMaskedCc,
            '{crAgent}' => $imporsonateMsg
        ];
        if ($isFrontend!="") {
            $this->message .= '<tr><td>'.strtr($message, $variables).'<br></td></tr>';
        } else {
            $this->message .= strtr($message, $variables).'<br>';
        }
    }

    /**
     * @param $oldValue
     * @param $newValue
     * @param $createdAt
     * @param $event
     * @throws \Exception
     */
    public function getProfileStatusChangeMessage($row, $event, $isFrontend = '')
    {

        if ($event == self::MBO_PROFILE_PAUSE) {
            $message = $this->getConfigMessage(self::XML_CHANGE_PROFILE_STATUS_TO_PAUSE, $row->getStoreId());
        }
        if ($event == self::MBO_PROFILE_RESUME) {
            $message = $this->getConfigMessage(self::XML_CHANGE_PROFILE_STATUS_TO_RESUME, $row->getStoreId());
        }

        if ($event == self::MBO_PROFILE_CANCELLED || $event == self::PROFILE_CANCELLED_STATUS) {
            $message = $this->getConfigMessage(self::XML_CHANGE_PROFILE_STATUS_TO_CANCELLED, $row->getStoreId());
        }

        if ($event == self::CRON_PROFILE_CANCELLED) {
            $message = $this->getConfigMessage(
                self::XML_CHANGE_PROFILE_STATUS_TO_CANCELLED_BY_CRON,
                $row->getStoreId()
            );
        }
        $imporsonateMsg =  '';

        if ($row->getIsImpersonate() || (!empty($row->getMboUser() && !is_null($row->getMboUser())))) {
            $imporsonateMsg = self::MESSAGE;
        }

        $variables = [
            '{created_at}' => $this->getDateByTimeZone($row->getCreatedAt()),
            '{crAgent}' => $imporsonateMsg
        ];

        if ($isFrontend!="") {
            $this->message .= '<tr><td>'.strtr($message, $variables).'<br></td></tr>';
        } else {
            $this->message .= strtr($message, $variables).'<br>';
        }
    }

    /**
     * @param $path
     * @param $storeId
     * @return mixed
     */
    public function getConfigMessage($path, $storeId, $isFrontend = '')
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getProfilePlanChangeMessage($oldValue, $newValue, $row, $isFrontend = '')
    {
        $message = $this->getConfigMessage(self::XML_CHANGE_PROFILE_PLAN_CHANGE, $row->getStoreId());
        $imporsonateMsg = '';

        if ($row->getIsImpersonate() || (!empty($row->getMboUser() && !is_null($row->getMboUser())))) {
            $imporsonateMsg = self::MESSAGE;
        }

        $variables = [
            '{created_at}' => $this->getDateByTimeZone($row->getCreatedAt()),
            '{oldPlan}' => $oldValue['plan_name'],
            '{newPlan}' => $newValue['plan_name'],
            '{crAgent}' => $imporsonateMsg
        ];

        if ($isFrontend!="") {
            $this->message .= '<tr><td>'.strtr($message, $variables).'<br></td></tr>';
        } else {
            $this->message .= strtr($message, $variables).'<br>';
        }
    }

    public function getProfileProductRemoveMessage($oldValue, $newValue, $row, $isFrontend = '')
    {
        $message = $this->getConfigMessage(self::XML_PROFILE_REMOVE_PRODUCT, $row->getStoreId());
        $imporsonateMsg = '';
        if ($row->getIsImpersonate() || (!empty($row->getMboUser() && !is_null($row->getMboUser())))) {
            $imporsonateMsg = self::MESSAGE;
        }

        foreach ($oldValue as $key => $date) {
            if ($key != 'qty') {
                $variables = [
                    '{created_at}' => $this->getDateByTimeZone($row->getCreatedAt()),
                    '{sku}' => $key,
                    '{name}' => $oldValue[$key],
                    '{qty}' => number_format($oldValue['qty']),
                    '{crAgent}' => $imporsonateMsg
                ];
                if ($isFrontend!="") {
                    $this->message .= '<tr><td>'.strtr($message, $variables).'<br></td></tr>';
                } else {
                    $this->message .= strtr($message, $variables).'<br>';
                }
            }
        }
    }

    public function getProfileChangeProductMessage($oldValue, $newValue, $row, $isFrontend = '')
    {
        $message = $this->getConfigMessage(self::XML_PROFILE_CHANGE_PRODUCT, $row->getStoreId());
        $oldValueArray = array_keys($oldValue);
        $oldSku = $oldValueArray[0];
        $newValueArray = array_keys($newValue);
        $sku = $newValueArray[0];

        $imporsonateMsg = '';
        if ($row->getIsImpersonate() || (!empty($row->getMboUser() && !is_null($row->getMboUser())))) {
            $imporsonateMsg = self::MESSAGE;
        }

        $variables = [
            '{created_at}' => $this->getDateByTimeZone($row->getCreatedAt()),
            '{oldProductSku}' =>  $oldSku,
            '{oldProductName}' => $oldValue[$oldSku],
            '{oldProductQty}' => number_format($oldValue['qty']),
            '{newProductSku}' =>  $sku,
            '{newProductName}' => $newValue[$sku],
            '{newProductQty}' => number_format($newValue['qty']),
            '{crAgent}' => $imporsonateMsg
        ];
        if ($isFrontend!="") {
            $this->message .= '<tr><td>'.strtr($message, $variables).'<br></td></tr>';
        } else {
            $this->message = strtr($message, $variables).'<br>';
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
     * Get the subscription history data
     * @param $profileId
     * @return array
     */
    public function getSubscriptionHistoryData($profileId)
    {
        if ($profileId) {
            return $this->historyFactory->create()->getCollection()
                            ->addFieldToFilter('profile_id', ['eq' => $profileId])
                            ->setOrder('created_at', 'ASC');
        }
        return false;
    }

    public function getProfileCreateMessage($oldValue, $newValue, $row, $isFrontend = '')
    {
        $message = $this->getConfigMessage(self::XML_SUBSCRIPTION_CREATE_PROFILE, $row->getStoreId());
        $productData = '';

        $createdAt = $this->getDateByTimeZone($row->getCreatedAt());
        $productCount = count($oldValue);
        foreach ($oldValue as $key => $data):
            $dataArrayKey = array_keys($data);
            $sku = $dataArrayKey['0'];
            $productData .= $data[$sku].' '.$sku.', '.number_format($data['qty']);
            if ($productCount >1 && $key < ($productCount-1)) {
                $productData .= '<br>';
            }
        endforeach;
        $variables = [
            '{created_at}' => $createdAt,
            '{productData}' =>  $productData,
            '{crAgent}' => ($row->getIsImpersonate()) ? self::MESSAGE : ''
        ];
        if ($isFrontend!="") {
            $this->message .= '<tr><td>'.strtr($message, $variables).'<br></td></tr>';
        } else {
            $this->message = strtr($message, $variables).'<br>';
        }
    }
}
