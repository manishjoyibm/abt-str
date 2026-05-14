<?php

namespace Abbott\OneTrust\Observer;

use Abbott\OneTrust\Helper\Data;
use Abbott\OneTrust\Logger\Logger;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;

class OnetrustPostCheckoutConsent implements ObserverInterface
{
    private const TOPIC_NAME = 'onetrust.consent';

    private const SKIP_PAYMENT_METHODS = [
        'aw_sarp_braintree_recurring',
        'aw_sarp_braintree_paypal_recurring',
        'aw_sarp_braintree_googlepay_recurring',
        'aw_sarp_braintree_applepay_recurring'
    ];

    /**
     * @var PublisherInterface
     */
    protected $publisher;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * OneTrust Post checkout consents
     *
     * @param PublisherInterface $publisher
     * @param Json $json
     * @param Logger $logger
     * @param Data $helper
     * Data $helper
     */
    public function __construct(
        PublisherInterface $publisher,
        Json $json,
        Logger $logger,
        Data $helper
    ) {
        $this->publisher = $publisher;
        $this->json = $json;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * Execute Function
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();
        $storeId = $order->getStoreId();
        if ($this->helper->isModuleEnabled(ScopeInterface::SCOPE_STORE, $storeId) &&
            !in_array($payment->getMethod(), self::SKIP_PAYMENT_METHODS)
        ) {
            $orderAttributes = $order->getExtensionAttributes()->getAmastyOrderAttributes();
            $isEmployeeConsent = '0';
            $checkoutConsentArray = [];
            if (!empty($orderAttributes)) {
                foreach ($orderAttributes as $attribute) {
                    if ($attribute->getAttributeCode() == 'employee_terms') {
                        $isEmployeeConsent = $attribute->getValue();
                        break;
                    }
                }
            }
            if ($isEmployeeConsent) {
                $checkoutConsentArray["checkout_employee_consent"] = '1';
            }

            //order placed from Backend
            $pageType = 'MBO_CHECKOUT_CONSENT';

            if (!empty($order->getRemoteIp())) {
                //order placed from Frontend
                $checkoutConsentArray["checkout_payment_consent"] = '1';
                $pageType = 'CHECKOUT_CONSENT';
            }

            $this->postConsent($checkoutConsentArray, $order->getCustomerEmail(), $pageType);
        }
    }

    /**
     * Post Consent to OneTrust
     *
     * @param mixed[] $checkoutConsentArray
     * @param string $email
     * @param string $pageType
     * @return void
     */
    private function postConsent($checkoutConsentArray, $email, $pageType): void
    {
        if (!empty($checkoutConsentArray)) {
            try {
                $rawData = [
                    'email' => $email,
                    'consentData' => $checkoutConsentArray,
                    'pageType' => $pageType
                ];

                $this->publisher->publish(self::TOPIC_NAME, $this->json->serialize($rawData));
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
