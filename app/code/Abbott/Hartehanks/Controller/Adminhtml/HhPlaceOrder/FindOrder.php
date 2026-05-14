<?php

namespace Abbott\Hartehanks\Controller\Adminhtml\HhPlaceOrder;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Abbott\Hartehanks\Model\HartehankFindOrderSync;
use Abbott\Hartehanks\Helper\Transport;

class FindOrder extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    public $transportHelper;
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Abbott_Hartehanks::findorder';

    protected $hhFindOrderSync;

    public function __construct(
        Context $context,
        HartehankFindOrderSync $hhFindOrderSync,
        Transport $transportHelper
    ) {
        $this->hhFindOrderSync = $hhFindOrderSync;
        $this->transportHelper = $transportHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->transportHelper->getFindOrderTest()) {
            $this->hhFindOrderSync->findOrderTest($this->transportHelper->getFindOrderId());
        } else {
            $this->hhFindOrderSync->execute();
        }

        return $resultRedirect->setPath('abbott_hartehanks/hhplaceorder/index');
    }
}
