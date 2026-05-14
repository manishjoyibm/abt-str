<?php

namespace Abbott\WorkdayFeed\Controller\Adminhtml\InboundFeed;

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
        \Abbott\WorkdayFeed\Model\FileUploader $fileUploader
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
        return $this->_authorization->isAllowed('Abbott_WorkdayFeed::inboundFeed');
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

            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
