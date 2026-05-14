<?php

namespace Abbott\WorkdayFeed\Controller\Adminhtml\InboundFeed;

use Abbott\ProductInfo\Model\InboundFeed;
use Abbott\WorkdayFeed\Api\InboundFeedRepositoryInterface;
use Abbott\WorkdayFeed\Model\InboundFeedFactory;
use Abbott\WorkdayFeed\Model\InboundFeedRepository;
use Abbott\WorkdayFeed\Model\WorkdaySync;
use Exception;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Abbott_WorkdayFeed::save';

    public const FILE_NAME = 'file_name';
    public const FEEDID = 'feed_id';

    /**
     * @var DataPersistorInterface
     */
    protected DataPersistorInterface $dataPersistor;

    /**
     * @var InboundFeedFactory
     */
    private mixed $inboundfeedFactory;

    /**
     * @var InboundFeedRepositoryInterface
     */
    private mixed $inboundfeedRepository;

    /**
     * @var WorkdaySync
     */
    private WorkdaySync $workdaySync;

    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     * @param DataPersistorInterface $dataPersistor
     * @param InboundFeedFactory|null $inboundfeedFactory
     * @param InboundFeedRepositoryInterface|null $inboundfeedRepository
     */
    public function __construct(
        Action\Context $context,
        DataPersistorInterface $dataPersistor,
        WorkdaySync $workdaySync,
        InboundFeedFactory $inboundfeedFactory = null,
        InboundFeedRepositoryInterface $inboundfeedRepository = null
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->inboundfeedFactory = $inboundfeedFactory
            ?: ObjectManager::getInstance()->get(
                InboundFeedFactory::class
            );
        $this->inboundfeedRepository = $inboundfeedRepository
            ?: ObjectManager::getInstance()
                ->get(InboundFeedRepository::class);
        $this->workdaySync = $workdaySync;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {

            if (empty($data[self::FEEDID])) {
                $data[self::FEEDID] = null;
            }

            /** @var InboundFeed $model */
            $model = $this->inboundfeedFactory->create();

            $id = $this->getRequest()->getParam(self::FEEDID);
            if ($id) {
                try {
                    $model = $this->inboundfeedRepository->getById($id);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('This Feed no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }
            $data = $this->_filterFoodData($data);

            $model->setData($data);
            $this->_eventManager->dispatch(
                'abbott_workfeed_prepare_save',
                ['inboundfeed' => $model, 'request' => $this->getRequest()]
            );

            try {
                $this->inboundfeedRepository->save($model);
                $this->workdaySync->gridReader($model->getFeedId());
                $this->messageManager->addSuccessMessage(__('You Processed The Feed.'));
                return $this->processResultRedirect($resultRedirect, $data);
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Feed.'));
            }

            $this->dataPersistor->set('abbott_workdayfeed', $data);
            return $resultRedirect->setPath('*/*/');
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Process result redirect
     *
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     * @param array $data
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws LocalizedException
     */
    private function processResultRedirect($resultRedirect, $data)
    {
        if ($this->getRequest()->getParam('back', false) === 'duplicate') {
            $newPage = $this->inboundfeedFactory->create(['data' => $data]);
            $newPage->getFeedId();
            $this->inboundfeedRepository->save($newPage);
            $this->messageManager->addSuccessMessage(__('You duplicated the page.'));
            return $resultRedirect->setPath('*/*/');
        }
        $this->dataPersistor->clear('abbott_workdayfeed');
        if ($this->getRequest()->getParam('back')) {
            return $resultRedirect->setPath('*/*/');
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Get filterFoodData
     *
     * @param array $rawData
     * @return array
     */
    public function _filterFoodData(array $rawData)
    {
        $data = $rawData;
        if (isset($data[self::FILE_NAME][0]['name'])) {
            $data[self::FILE_NAME] = $data[self::FILE_NAME][0]['name'];
        } else {
            $data[self::FILE_NAME] = null;
        }
        return $data;
    }
}
