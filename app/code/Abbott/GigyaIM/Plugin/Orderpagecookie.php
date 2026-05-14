<?php
namespace Abbott\GigyaIM\Plugin;

use Abbott\AwsLambda\Logger\Log as Logger;
use Abbott\GigyaIM\Helper\Data as GigyaHelper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;

class Orderpagecookie
{
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var gigyaHelper
     */
    protected $gigyaHelper;
    public const ABT_USR = 'abt_usr';
    /**
     * CoustomerAttributePlugin constructor
     *
     * @param GigyaHelper $gigyaHelper
     * @param Logger $logger
     */
    public function __construct(
        GigyaHelper $gigyaHelper,
        Logger $logger
    ) {
        $this->logger = $logger;
        $this->gigyaHelper = $gigyaHelper;
    }

    /**
     * Before plugin for Place method.
     *
     * @param OrderManagementInterface $subject
     * @param OrderInterface $result
     * @return OrderInterface
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    public function afterPlace(OrderManagementInterface $subject, OrderInterface $result)
    {
        $orderId = $result->getIncrementId();
        if ($orderId) {
            $this->logger->writeLog('Order Place init##############');
            $abt_usr = json_decode($this->gigyaHelper->getCustomCookie('abt_usr') ?? "", true);
            if ($this->gigyaHelper->getCustomCookie('abt_usr')) {
                $abt_usr['magento_page']['orders'] = 1;
                $this->gigyaHelper->setCookie('abt_usr', json_encode($abt_usr));
                $this->logger->writeLog(self::ABT_USR.' cookie set');
            }
            $this->logger->writeLog('After Order Done##############');
        }
        return $result;
    }
}
