<?php

namespace Abbott\Chargeback\Controller\Adminhtml\Chargeback;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Abbott\Chargeback\Helper\Data;

class Validate extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * @var Data
     */
    protected Data $helper;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $jsonFactory;

    /**
     * @param Context $context
     * @param Data $helper
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        Data $helper,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->helper = $helper;
    }

    /**
     * Execute
     *
     * @return Json
     */
    public function execute()
    {
        $resultPage = $this->jsonFactory->create();
        $status = $this->helper->sftpValidator();
        return $resultPage->setData($status);
    }
}
