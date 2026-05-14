<?php

namespace Abbott\Hartehanks\Controller\Adminhtml\HhPlaceOrder;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Abbott\Hartehanks\Model\HartehankPlaceOrderSync;
use Abbott\Hartehanks\Helper\Transport;

class PlaceOrder extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    public $transportHelper;
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Abbott_Hartehanks::placeorder';

    protected $hhPlaceOrderSync;

    public function __construct(
        Context $context,
        HartehankPlaceOrderSync $hhPlaceOrderSync,
        Transport $transportHelper
    ) {
        $this->hhPlaceOrderSync = $hhPlaceOrderSync;
        $this->transportHelper = $transportHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->transportHelper->testEnable()) {
            $this->hhPlaceOrderSync->executeWithoutLimit($this->transportHelper->getPlaceOrderId());
        } else {
            $this->hhPlaceOrderSync->execute();
        }
        return $resultRedirect->setPath('abbott_hartehanks/hhplaceorder/index');
    }
}
