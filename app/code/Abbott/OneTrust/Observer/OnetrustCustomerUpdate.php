<?php

namespace Abbott\OneTrust\Observer;

use Abbott\OneTrust\Logger\Logger;
use Abbott\OneTrust\Helper\Data;
use Exception;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\ScopeInterface;

class OnetrustCustomerUpdate implements ObserverInterface
{
    private const TOPIC_NAME = 'onetrust.consent';

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
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * OneTrust CustomerUpdate from MBO constructor.
     *
     * @param PublisherInterface $publisher
     * @param Json $json
     * @param Logger $logger
     * @param Data $helper
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(
        PublisherInterface $publisher,
        Json $json,
        Logger $logger,
        Data $helper,
        SubscriberFactory $subscriberFactory
    ) {
        $this->publisher = $publisher;
        $this->json = $json;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $customer = $observer->getEvent()->getCustomer();
        $storeId = $customer->getStoreId();
        if ($this->helper->isModuleEnabled(ScopeInterface::SCOPE_STORE, $storeId)) {
            $subscriptionStatus = (array)$observer->getEvent()->getRequest()->getParam('subscription_status');
            $customerId = $this->getCurrentCustomerId($observer->getEvent()->getRequest());
            $websiteId = $customer->getWebsiteId();
            if (!empty($subscriptionStatus)) {
                $subscriber = $this->loadSubscriberByCustomer($customer, $websiteId);
                $currentStatus = (int)$subscriber->getStatus();

                $consentArray = [
                    'mbo_newsletter_subscriber' => $subscriptionStatus[$websiteId]
                ];

                if (!$currentStatus) {
                    $consentArray = [
                        'mbo_newsletter_subscriber' => 'NA'
                    ];
                }

                $rawData = [
                    'email' => $customer->getEmail(),
                    'consentData' => $consentArray,
                    'pageType' => 'MBO_CUSTOMER_UPDATE'
                ];
            } elseif (!$customerId) {
                $rawData = [
                    'email' => $customer->getEmail(),
                    'new_customer_mbo' => true
                ];
            }
            if (!empty($rawData)) {
                try {
                    $this->publisher->publish(self::TOPIC_NAME, $this->json->serialize($rawData));
                } catch (Exception $e) {
                    $this->logger->critical($e->getMessage());
                }
            }
        }
    }

    /**
     * Retrieve current customer ID
     *
     * @param $request
     * @return int|null
     */
    private function getCurrentCustomerId($request): ?int
    {
        $originalRequestData = $request->getPostValue(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);

        return $originalRequestData['entity_id'] ?? null;
    }

    /**
     * Load subscriber model by customer
     *
     * @param CustomerInterface $customer
     * @param int $websiteId
     * @return Subscriber
     */
    private function loadSubscriberByCustomer(CustomerInterface $customer, int $websiteId): Subscriber
    {
        $subscriber = $this->subscriberFactory->create();
        $subscriber->loadByCustomer((int)$customer->getId(), $websiteId);
        if (!$subscriber->getId()) {
            $subscriber->loadBySubscriberEmail((string)$customer->getEmail(), $websiteId);
        }

        return $subscriber;
    }
}
