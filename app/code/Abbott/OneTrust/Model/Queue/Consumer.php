<?php

namespace Abbott\OneTrust\Model\Queue;

use Abbott\OneTrust\Helper\Api;
use Abbott\OneTrust\Logger\Logger;
use Abbott\OneTrust\Model\OneTrust;
use Exception;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Consumer
 */
class Consumer
{
    /**
     * @var Api
     */
    private $helperApi;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var OneTrust
     */
    private $oneTrust;

    /**
     * @param Api $helperApi
     * @param Json $json
     * @param Logger $logger
     * @param OneTrust $oneTrust
     */
    public function __construct(
        Api $helperApi,
        Json $json,
        Logger $logger,
        OneTrust $oneTrust
    ) {
        $this->helperApi = $helperApi;
        $this->json = $json;
        $this->logger = $logger;
        $this->oneTrust = $oneTrust;
    }

    /**
     * @param $rawData
     * @return void
     */
    public function process($rawData): void
    {
        try {
            $data = $this->json->unserialize($rawData);
            $email = $data['email'];
            if (isset($data['new_customer_mbo']) && $data['new_customer_mbo']) {
                $this->logger->info('OneTrust : Creating Data Subject via Queue. Email: ' . $email);
                $this->oneTrust->createCustomerInOneTrust($email);
                return;
            }

            $consentData = $data['consentData'];
            $pageType = $data['pageType'];
            $this->logger->info('OneTrust : Processing Consents via Queue. PageType: ' . $pageType);
            $this->helperApi->postConsentToOneTrust($email, $consentData, $pageType);
        } catch (Exception $e) {
            $this->logger->critical('OneTrust API Failed. ' . $e->getMessage());
        }
    }
}
