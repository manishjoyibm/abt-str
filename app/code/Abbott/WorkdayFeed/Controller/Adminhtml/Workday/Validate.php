<?php

namespace Abbott\WorkdayFeed\Controller\Adminhtml\Workday;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Abbott\WorkdayFeed\Helper\InboundFeedHelper;

class Validate extends Action implements HttpPostActionInterface
{
    /**
     * @var InboundFeedHelper
     */
    protected InboundFeedHelper $inboundFeedHelper;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $jsonFactory;

    /**
     * @param Context $context
     * @param InboundFeedHelper $inboundFeedHelper
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        InboundFeedHelper $inboundFeedHelper,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->inboundFeedHelper = $inboundFeedHelper;
    }

    /**
     * Execute method
     */
    public function execute()
    {
        $resultPage = $this->jsonFactory->create();
        $status = $this->inboundFeedHelper->SFTPValidator();
        return $resultPage->setData($status);
    }
}
