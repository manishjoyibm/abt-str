<?php


namespace Abbott\GPAS\Api;


use Abbott\GPAS\Api\Data\QrCodeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface QrCodeManagerInterface
 * @package Abbott\GPAS\Api
 */
interface QrCodeManagerInterface
{

    /**
     * @param string $code
     * @param string $ip
     * @param int $customerId
     * @param null $lat
     * @param null $long
     * @return \Abbott\GPAS\Api\Data\QrCodeInterface
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function processInit($code, $ip, $customer, $lat, $long);

    /**
     * @param OrderInterface $order
     * @return boolean
     */
    public function processSale(OrderInterface $order);
}
