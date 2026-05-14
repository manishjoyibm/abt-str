<?php

namespace Abbott\Checkout\Helper;

use Magento\Framework\Stdlib\CookieManagerInterface as CookieManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Abbott\MetabolicOrdering\Helper\Data as MetabolicData;

class Data
{

    public $scopeConfig;
    public $transportBuilder;
    public $cookieManagerInterface;
    public $helper;
    const TO_EMAIL = 'my_account/notification/to_email';

    const SUB_TYPE = 'aw_sarp2_subscription_type';
    const BACKORDER = 4;
    public const XML_PATH_MYACCOUNT_FAILURE_REDIRECT = 'my_account/myAccount_redirect/redirect_failure_url';

    /**
     * @var RequestInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResource;

    protected $productloader;

    protected $timezoneInterface;

    protected $metabolicData;

    /**
     *
     * @var \Abbott\StockManagement\Helper\Data
     */
    protected $dataHelper;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param Data $helper
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Abbott\StockManagement\Helper\Data $dataHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Abbott\Sarp2\Helper\Data $helper,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Catalog\Model\ProductFactory $productloader,
        CookieManagerInterface $cookieManagerInterface,
        MetabolicData $metabolicData,
        TimezoneInterface $timezoneInterface
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->productResource = $productResource;
        $this->dataHelper = $dataHelper;
        $this->cookieManagerInterface = $cookieManagerInterface;
        $this->helper = $helper;
        $this->productloader = $productloader;
        $this->metabolicData = $metabolicData;
        $this->timezoneInterface = $timezoneInterface;
    }

    public function sendAdminNotification($message)
    {
        $customerName = "Admin";
        $receiverEmail = ($this->getAdminEmail() != "") ? $this->getAdminEmail() : "AbbottstoreAppAlerts@abbott.com";
        $store = $this->storeManager->getStore();
        $url = $this->helper->getStoreUrl();
        $storePhone = $this->helper->getStorePhone();
        $storeName = $this->storeManager->getStore()->getFrontendName();
        $templateVars = [
            'store' => $store,
            'errorMessage'    => $message,
            'storeName' => $storeName,
            'url' => $url,
            'customerEmail' => $receiverEmail,
            'customerName' => $customerName,
            'storePhone' => $storePhone
        ];
        $sender = $this->helper->getSenderEmail();
        $transport = $this->transportBuilder
            ->setTemplateIdentifier("delay_customer_creation_failuare")
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId(),
                ]
            )->setTemplateVars(
                $templateVars
            )->setFrom(
                $sender
            )->addTo(
                $receiverEmail
            )->getTransport();
        $transport->sendMessage();
    }

    public function getAdminEmail()
    {
        return $this->scopeConfig->getValue(
            self::TO_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * Validate product quantity
     */
    public function isEnabledQuantityValidation()
    {
        return $this->scopeConfig->getValue(
            'my_account/cart_qty_limit/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }


    public function validateProductQuantityForMetabolic($sku, $qty, $customerEmail)
    {
        $message = '';
        $data['sku'] = $sku;
        $data['customer_email'] = $customerEmail;
        $metabolicDataResult = $this->metabolicData->ifExistingRecord($data);
        $currentDate = $this->timezoneInterface->date()->format('Y-m-d');
        if ($qty > $metabolicDataResult['qty']) {
            $message =
                "The most you may purchase is " . $metabolicDataResult['qty'];
        } elseif ($currentDate >= $metabolicDataResult['expiry_date']) {
            $message =
                "Pruchase date for Metabolic Product is expired.";
        }
        return $message;
    }


    public function validateMetabolicOrderingProductAfterOrder($customerEmail, $productSku)
    {
        $resultData = [];
        $data['sku'] = $productSku;
        $data['customer_email'] = $customerEmail;
        $metabolicData = $this->metabolicData->ifExistingRecord($data);
        if (isset($metabolicData['entity_id'])) {
            $resultData['entity_id'] = $metabolicData['entity_id'];
            $resultData['qty'] = $metabolicData['qty'];
        }
        return $resultData;
    }


    /**
     * Validate product quantity
     */
    public function validateProductQuantity($productId, $qty, $name = '', $storeId = '', $showMessage = null)
    {
        $message = '';
        if ($productId) {
            $productData = $this->productloader->create()->load($productId);
            $storeId = ($storeId) ? $storeId : $this->storeManager->getStore()->getId();
            $minQty = $this->productResource->getAttributeRawValue($productId, 'cans_y_min_update', $storeId);
            $minActualQty = $this->productResource->getAttributeRawValue($productId, 'cans_y_min_update', $storeId);
            $maxQty = $this->productResource->getAttributeRawValue($productId, 'cans_x_max_update', $storeId);
            $minQty = ($minQty != "") ? intval($minQty) : $minQty;
            $maxQty = ($maxQty != "") ? intval($maxQty) : $maxQty;
            $minQty = (!empty($minQty) && is_int($minQty)) ? $minQty : 1;
            $maxQty = (!empty($maxQty) && is_int($maxQty)) ? $maxQty : '';
            $sku = $productData->getSku();
            $flag = 0;

            if ($this->dataHelper->getConfigValue() &&
                $this->dataHelper->checkStock($productData) == self::BACKORDER &&
                $productData->getData()['quantity_and_stock_status']['is_in_stock']
            ) {
                $threshold = $this->productResource->getAttributeRawValue($productId, 'threshold', $storeId);
                $productQty = $productData->getData()['quantity_and_stock_status']['qty'];
                $diff = $productQty  - $threshold;
                if ($diff < $maxQty && $qty > $diff) {
                    $flag = 1;
                    if ($showMessage != null) {
                        $message  = 'Could not add the product with SKU ' . $sku .
                            ' to the shopping cart. The requested quantity is not available.';
                    } else {
                        $message  = 'Could not update the product with SKU ' . $sku .
                            ' to the shopping cart. The requested quantity is not available.';
                    }
                }
            }

            if ($qty < $minQty || ($maxQty != "" && $qty > $maxQty) && !$flag && ($minActualQty > 0)) {
                $message = ($name) ? "Min " . $minQty .
                    " and Max " . $maxQty . " Quantity required for product " .
                    $name : "Min " . $minQty . " and Max " . $maxQty . " Quantity required";
            } elseif (($minActualQty == 0 || $minActualQty =="") && ($maxQty != "" && $qty > $maxQty) && !$flag) {
                $message = "The most you may purchase is " . $maxQty;
            }
        }
        return $message;
    }

    public function validateItemsQuantity($quoteItems, $storeId)
    {
        if (!empty($quoteItems)) {
            foreach ($quoteItems as $item):
                if ($this->getSubscriptionOption($item)) {
                    //add product min and max quantity validation for
                    return $this->validateProductQuantity(
                        $item->getProductId(),
                        $item->getQty(),
                        $item->getName(),
                        $storeId
                    );
                }
            endforeach;
        }
    }

    public function getSubscriptionOption($cartItem)
    {
        $options = $cartItem->getOptionsByCode();
        if (array_key_exists(self::SUB_TYPE, $options)) {
            return true;
        }
        return false;
    }

    public function isSSMSubscriptionProgramEnabled()
    {
        return $this->scopeConfig->getValue(
            'my_account/subscription_program/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    public function getSSMRegistrationUrl()
    {
        return $this->scopeConfig->getValue(
            'my_account/subscription_program/ssm_registration_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    public function getNonSsmCheckoutMessage()
    {
        return $this->scopeConfig->getValue(
            'aboott_message/cart_error_message/non_ssm_message_for_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    public function getSSMMinicartPrgMessage()
    {
        return $this->scopeConfig->getValue(
            'aboott_message/minicart_error_message/ssm_message_for_prg_minicart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    public function getSSMMinicartMessage()
    {
        return $this->scopeConfig->getValue(
            'aboott_message/minicart_error_message/ssm_message_for_minicart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }


    public function getNonSSMMinicartPrgMessage()
    {
        return $this->scopeConfig->getValue(
            'aboott_message/minicart_error_message/non_ssm_message_for_prg_minicart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    public function getGuestMinicartPrgMessage()
    {
        return $this->scopeConfig->getValue(
            'aboott_message/minicart_error_message/guest_message_for_prg_minicart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    public function getGuestMinicartMessage()
    {
        return $this->scopeConfig->getValue(
            'aboott_message/minicart_error_message/guest_message_for_minicart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    public function getNonSSMMinicartMessage()
    {
        return $this->scopeConfig->getValue(
            'aboott_message/minicart_error_message/non_ssm_message_for_minicart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    public function getXIdToken()
    {
        return $this->cookieManagerInterface->getCookie('x-id-token');
    }

    public function checkCartItemsForPlan($quoteItems)
    {
        if (!empty($quoteItems)) {
            foreach ($quoteItems as $item):
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                if (!empty($options)) {
                    $itemPlanId = (isset(
                        $options['aw_sarp2_subscription_plan']
                    )) ? $options['aw_sarp2_subscription_plan']['plan_id'] : '';
                    if (!empty($itemPlanId)) {
                        return true;
                    }
                }
            endforeach;
        }
        return false;
    }
    /**
    * Get AemUrl
    *
    * @return mixed
    */
    public function getAemUrl(): mixed
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MYACCOUNT_FAILURE_REDIRECT,
           \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

}
