<?php

namespace Abbott\WorkdayFeed\Controller\Adminhtml\InboundFeed;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Abbott\WorkdayFeed\Model\WorkdaySync;

class WorkdaySFTPReader extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    public $workdaySync;
    protected $hartHankSync;

    public function __construct(
        Context $context,
        WorkdaySync $hartHankSync
    ) {
        $this->workdaySync = $hartHankSync;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->workdaySync->execute();
        return $resultRedirect->setPath('abbott/inboundfeed/index');
    }
}
