<?php

namespace Abbott\Hartehanks\Model\Queue;

use Abbott\Hartehanks\Model\Method\Logger;
use Exception;
use Abbott\Hartehanks\Model\HartehankPlaceOrderSync;
use Abbott\Hartehanks\Model\HartehankFindOrderSync;
use Magento\Framework\Serialize\Serializer\Json;

class Consumer
{
    /**
     * @var HartehankPlaceOrderSync
     */
    private HartehankPlaceOrderSync $hartehankPlaceOrderSync;

    /**
     * @var HartehankFindOrderSync
     */
    private HartehankFindOrderSync $hartehankFindOrderSync;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @param HartehankPlaceOrderSync $hartehankPlaceOrderSync
     * @param HartehankFindOrderSync $hartehankFindOrderSync
     * @param Logger $logger
     * @param Json $json
     */
    public function __construct(
        HartehankPlaceOrderSync $hartehankPlaceOrderSync,
        HartehankFindOrderSync $hartehankFindOrderSync,
        Logger $logger,
        Json $json,
    ) {
        $this->hartehankPlaceOrderSync = $hartehankPlaceOrderSync;
        $this->hartehankFindOrderSync = $hartehankFindOrderSync;
        $this->logger = $logger;
        $this->json = $json;
    }

    /**
     * Process PlaceOrder Sync
     *
     * @param string $rawData
     * @return void
     */
    public function process(string $rawData): void
    {
        try {
            $data = $this->json->unserialize($rawData);
            $xmlPostString = $data['xmlPostString'];
            $orderIds = $data['orderIds'];
            $this->logger->debug('HarteHanks : Processing Orders via Queue. OrderIds: ', $orderIds);
            $this->hartehankPlaceOrderSync->sendOrdersToHH($xmlPostString, $orderIds);
        } catch (Exception $e) {
            $this->logger->debug('HarteHanks API Failed. ', $e->getMessage());
        }
    }

    /**
     * Process FindOrder Sync
     *
     * @param string $rawData
     * @return void
     */
    public function processFindOrder(string $rawData): void
    {
        try {
            $data = $this->json->unserialize($rawData);
            $orderIds = $data['orderIds'];
            $this->logger->debug('HarteHanks : Processing Find Orders Data via Queue. OrderIds: ', $orderIds);
            $this->hartehankFindOrderSync->findOrderCollection(null, $orderIds);
        } catch (Exception $e) {
            $this->logger->debug('HarteHanks API Failed. ', $e->getMessage());
        }
    }
}
