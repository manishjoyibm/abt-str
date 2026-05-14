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

class OnetrustCustomerRegistration implements ObserverInterface
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
     * OneTrust CustomerRegistration Frontend constructor.
     *
     * @param PublisherInterface $publisher
     * @param Json $json
     * @param Logger $logger
     * @param Data $helper
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
            $isSub = $observer->getEvent()->getAccountController()->getRequest()->getParam('is_subscribed');
            $subs = ($isSub) ? '1' : 'NA';

            $newsLetArray = [
                "newsletter_subscriber" => $subs
            ];

            $rawData = [
                'email' => $customer->getEmail(),
                'consentData' => $newsLetArray,
                'pageType' => 'CUSTOMER_REGISTRATION'
            ];

            try {
                $this->publisher->publish(self::TOPIC_NAME, $this->json->serialize($rawData));
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
