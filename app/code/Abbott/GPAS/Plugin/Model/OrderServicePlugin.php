<?php


namespace Abbott\GPAS\Plugin\Model;


use Abbott\GPAS\Api\QrCodeManagerInterface;
use Abbott\GPAS\Helper\Data;
use Abbott\GPAS\Logger\Logger;
use Abbott\GPAS\Model\Attribute\Product\IsGpas;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class OrderServicePlugin
 * @package Abbott\GPAS\Plugin\Model
 */
class OrderServicePlugin
{
    /**
     * @var QrCodeManagerInterface
     */
    private $qrCodeManager;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * OrderServicePlugin constructor.
     * @param QrCodeManagerInterface $qrCodeManager
     * @param Logger $logger
     * @param Data $helper
     */
    public function __construct(QrCodeManagerInterface $qrCodeManager, Logger $logger, Data $helper, StoreManagerInterface $storeManager) {

        $this->qrCodeManager = $qrCodeManager;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Sales\Model\Service\OrderService $subject
     * @param OrderInterface $order
     */
    public function afterPlace(\Magento\Sales\Model\Service\OrderService $subject, OrderInterface $order) {
        try {
            if ($this->helper->isEnabled($this->storeManager->getStore()->getId())) {
                $gpasOrder = false;
                if($items = $order->getItems()) {
                    foreach ($items as $item) {
                        if ($item->getProduct()->getData(IsGpas::ATTRIBUTE_CODE)) {
                            $gpasOrder = true;
                            break;
                        }
                    }
                }
                if($gpasOrder) {
                    $this->qrCodeManager->processSale($order);
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $order;
    }

}
