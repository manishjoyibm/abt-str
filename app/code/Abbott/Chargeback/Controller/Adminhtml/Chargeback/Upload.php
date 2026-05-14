<?php

namespace Abbott\Chargeback\Controller\Adminhtml\Chargeback;

use Magento\Framework\Controller\ResultFactory;

class Upload extends \Magento\Backend\App\Action
{
    /**
     * File uploader
     *
     * @var \Abbott\WorkdayFeed\Model\FileUploader
     */
    public $fileUploader;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Abbott\WorkdayFeed\Model\FileUploader $fileUploader
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Abbott\Chargeback\Model\FileUploader $fileUploader
    ) {
        parent::__construct($context);
        $this->fileUploader = $fileUploader;
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Abbott_Chargeback::Chargeback');
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $result = $this->fileUploader->saveFileToTmpDir('file_name');
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
