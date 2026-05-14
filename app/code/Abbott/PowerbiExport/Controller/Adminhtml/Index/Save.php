<?php
namespace Abbott\PowerbiExport\Controller\Adminhtml\Index;

use Abbott\PowerbiExport\Helper\Powerbi as PowerbiHelper;
use Abbott\PowerbiExport\Model\PowerbiFactory;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Save extends Action
{
    /**
     * @var TimezoneInterface
     */
    protected TimezoneInterface $timezoneInterface;

    /**
     * @var PowerbiHelper
     */
    protected PowerbiHelper $powerbiHelper;

    /**
     * @var PowerbiFactory
     */
    protected PowerbiFactory $powerbiModelFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param TimezoneInterface $timezoneInterface
     * @param PowerbiHelper $powerbiHelper
     * @param PowerbiFactory $powerbiModelFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        TimezoneInterface $timezoneInterface,
        PowerbiHelper $powerbiHelper,
        PowerbiFactory $powerbiModelFactory
    ) {
        $this->timezoneInterface = $timezoneInterface;
        $this->powerbiHelper = $powerbiHelper;
        $this->powerbiModelFactory = $powerbiModelFactory;
        parent::__construct($context);
    }

    /**
     * Check if user has permissions to access this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Abbott_PowerbiExport::save");
    }

    /**
     * Save PowerBI action
     *
     * @return ResultInterface
     * @throws LocalizedException
     * @throws Exception
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        $reportId = $this->getRequest()->getParam('report_id');

        if ($reportId && empty($this->powerbiHelper->ifExistingRecord($data)) || isset($data['entity_id'])) {
            $this->performSave($data);
            $this->messageManager->addSuccess(__('Record saved'));
        } else {
            $this->messageManager->addError(__('Report already exists with this record'));
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Perform save
     *
     * @param mixed[] $data
     * @return void
     * @throws Exception
     */
    private function performSave(array $data): void
    {
        $powerbiData = $this->powerbiModelFactory->create();
        if (isset($data['entity_id'])) {
            $powerbiData->load($data['entity_id']);
        }
        $powerbiData->setData($data);
        $powerbiData->save();
    }
}
