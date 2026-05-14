<?php

namespace Abbott\Hartehanks\Controller\Adminhtml\HarteHank;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Abbott\Hartehanks\Model\HartehankSync;

class SyncInventory extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Abbott_Hartehanks::syncinventory';

    protected $hartHankSync;

    public function __construct(
        Context $context,
        HartehankSync $hartHankSync
    ) {
        $this->hartHankSync = $hartHankSync;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->hartHankSync->execute();
        return $resultRedirect->setPath('abbott_hartehanks/hartehank/index');
    }
}
