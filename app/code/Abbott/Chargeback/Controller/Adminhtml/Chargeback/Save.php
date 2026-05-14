<?php

namespace Abbott\Chargeback\Controller\Adminhtml\Chargeback;

use Abbott\Chargeback\Model\ChargebackSync;
use Abbott\WorkdayFeed\Model\InboundFeedFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Abbott\Chargeback\Helper\Data as DataHelper;

class Save extends \Magento\Backend\App\Action
{
    public $inboundFeedFactory;
    public $chargebackSync;
    public $chargebackHelper;
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Abbott_Chargeback::save';

    private const FILE_NAME = 'file_name';

    /**
     * @var DataPersistorInterface
     */
    protected DataPersistorInterface $dataPersistor;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param InboundFeedFactory $inboundFeedFactory
     * @param ChargebackSync $chargebackSync
     * @param DataHelper $chargebackHelper
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        InboundFeedFactory $inboundFeedFactory,
        ChargebackSync $chargebackSync,
        DataHelper $chargebackHelper
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->inboundFeedFactory = $inboundFeedFactory;
        $this->chargebackSync = $chargebackSync;
        $this->chargebackHelper = $chargebackHelper;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $model = $this->inboundFeedFactory->create();
            $data = $this->filterFileData($data);
            $summaryData = ['ChargeBack',$data[self::FILE_NAME],'Pending','No Records Added Yet'];
            try {
                $model->submitReport($summaryData);
                $this->chargebackSync->updateStatus($model->getId());
                $this->chargebackHelper->chargebackFiles($model->getFileName(), true);
                $this->messageManager->addSuccessMessage(__('You Processed the Chargeback Data'));
                $this->dataPersistor->clear('abbott_chargeback_log');
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, $e->getMessage());
            }

            $this->dataPersistor->set('abbott_chargeback_log', $data);
            return $resultRedirect->setPath('*/*/');
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Filter File Data
     *
     * @param array $data
     * @return array
     */
    public function filterFileData(array $data): array
    {
        $data[self::FILE_NAME] = (isset($data[self::FILE_NAME][0]['name'])) ? $data[self::FILE_NAME][0]['name'] : null;
        return $data;
    }
}
