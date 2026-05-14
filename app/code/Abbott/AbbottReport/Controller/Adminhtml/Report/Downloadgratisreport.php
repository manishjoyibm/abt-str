<?php
namespace Abbott\AbbottReport\Controller\Adminhtml\Report;

use \Abbott\AbbottReport\Api\Data\AbbottExportInfoInterface;

/**
 *
 */
class Downloadgratisreport extends \Magento\Backend\App\Action
{

    public $objectManager;
    protected $publisher;

    protected $logger;

    protected $json;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\MessageQueue\Publisher $publisher,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\ObjectManagerInterface $objectManager


    ) {
        $this->publisher = $publisher;
        $this->logger = $logger;
        $this->json = $json;
        $this->objectManager = $objectManager;
        parent::__construct($context);
    }

    /**
     * This function is used to create gratis report
     *
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            $this->_view->loadLayout(false);
            $data = $this->getRequest()->getParams();
            $exportInfo = $this->objectManager->create(AbbottExportInfoInterface::class);
            if (isset($data['from_gratis']) && isset($data['to_gratis'])) {
                $exportInfo->setToGratis($data['to_gratis']);
                $exportInfo->setFromGratis($data['from_gratis']);
                $exportInfo->setStoreId($data['store_id']);
            }
            $this->publisher->publish('abbott.gratis.report', $exportInfo);
            $this->messageManager->addSuccessMessage(
                __(
                    'Message is added to queue, '.
                    'File will be available to download from System->Data Transfer->Export grid in 5 minutes.'
                    . ' Make sure your cron job is running to export the file'
                )
            );
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->messageManager->addError(__('An error occurred while adding report to Queue.'));
        }

        $this->_redirect('abbottreport/report/gratis');
    }
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
