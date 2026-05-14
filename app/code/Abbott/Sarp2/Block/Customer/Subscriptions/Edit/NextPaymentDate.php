<?php

namespace Abbott\Sarp2\Block\Customer\Subscriptions\Edit;

use Aheadworks\Sarp2\Model\DateTime\FormatConverter;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\Config;

class NextPaymentDate extends \Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\NextPaymentDate
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $orderModel;
    
    /**
     * @var Registry
     */
    protected $registry;
    
    /**
     * @param Context $context
     * @param Registry $registry
     * @param ProfileManagementInterface $profileManagement
     * @param Config $config
     * @param FormatConverter $dateFormatConverter
     * @param \Magento\Sales\Model\Order $orderModel
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProfileManagementInterface $profileManagement,
        Config $config,
        FormatConverter $dateFormatConverter,
        \Magento\Sales\Model\Order $orderModel,
        array $data = []
    ) {
        parent::__construct($context, $registry, $profileManagement, $config, $dateFormatConverter, $data);
        $this->registry = $registry;
        $this->orderModel = $orderModel;
    }
    
    /**
     * Gives if customer can change next payment date
     *
     * @return boolean
     */
    public function isNextPaymentDateAvailable()
    {
        $orderId = $this->getProfile()->getLastOrderId();
        $order = $this->orderModel->load($orderId);
        return $order->hasShipments();
    }
    
    /**
     * Get profile
     *
     * @return ProfileInterface
     */
    private function getProfile()
    {
        return $this->registry->registry('profile');
    }
}
