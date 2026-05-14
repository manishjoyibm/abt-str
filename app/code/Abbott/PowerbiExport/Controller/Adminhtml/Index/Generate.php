<?php
namespace Abbott\PowerbiExport\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Abbott\PowerbiExport\Helper\Powerbi;
use Abbott\PowerbiExport\Model\PowerbiFactory as PowerbiFactory;
use Abbott\PowerbiExport\Logger\Method\Logger;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Generate powerbi report action.
 */
class Generate extends \Magento\Backend\App\Action
{
    public $_varDirectory;
    public $file;
    public $timezoneInterface;
    /**
     * @var Powerbi
     */
    private Powerbi $powerbiHelper;

    /**
     * @var PowerbiFactory
     */
    protected PowerbiFactory $powerbiModelFactory;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * Constructor
     * @param Context $context
     * @param Powerbi $powerbiHelper
     * @param PowerbiFactory $powerbiModelFactory
     * @param Logger $logger
     * @param Filesystem $filesystem
     * @param File $file
     * @param TimezoneInterface $timezoneInterface
     * @throws FileSystemException
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Powerbi $powerbiHelper,
        PowerbiFactory $powerbiModelFactory,
        Logger $logger,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Driver\File $file,
        TimezoneInterface $timezoneInterface
    ) {
        $this->powerbiHelper = $powerbiHelper;
        $this->powerbiModelFactory = $powerbiModelFactory;
        $this->logger = $logger;
        $this->_varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->file = $file;
        $this->timezoneInterface = $timezoneInterface;
        parent::__construct($context);
    }

    /**
     * Check if user has permissions to access this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Abbott_PowerbiExport::generate");
    }

    /**
     * Report generate action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($this->powerbiHelper->getPowerbiConfig(Powerbi::ENABLE_POWERBI_EXPORT)) {

            $powerbiEntityId  = (int)$this->getRequest()->getParam('entity_id');
            $reportGenerate = $this->powerbiModelFactory->create()->load($powerbiEntityId);
            $powerbiReportId  = (int)$reportGenerate->getReportId();
            $powerbiReportName = $reportGenerate->getReportName();

            /** @var Redirect $resultRedirect */
            if (!empty($powerbiReportId)) {
                try {
                    $response = $this->powerbiHelper->getReportResponse($powerbiReportId);
                    if (empty($response)) {
                        $this->messageManager->addErrorMessage(__('Can not export this report. Something went wrong'));
                        return $resultRedirect->setPath('powerbi_export');
                    }
                    try {
                        $this->logger->info(sprintf('Admin Export for report '.$powerbiReportId.' Started'));
                        $dumpFileName = str_replace(" ", "_", sprintf($powerbiReportName.'_%s.csv', date('d_m_y')));
                        $varDirectory = $this->_varDirectory->getAbsolutePath().'mbi/';
                        $dumpFile = $varDirectory . $dumpFileName;
                        $fileopen = $this->file->fileOpen($dumpFile, 'w');
                        fputs($fileopen, $response);
                        $this->logger->info("Report ID: ".$powerbiReportId." admin export done");
                        $dateTime = $this->timezoneInterface->date()->format('Y-m-d H:i:s');
                        $reportGenerate->setData('last_cron_update_date', $dateTime);
                        $reportGenerate->save();
                    }catch (LocalizedException $e) {
                        $this->logger->critical("exception while executing command " . $e->getMessage());
                    }
                    $this->messageManager->addSuccessMessage(__('Report successfully generated.'));
                } catch (\Exception $exception) {
                    $this->messageManager->addErrorMessage($exception->getMessage());
                }
                return $resultRedirect->setPath('powerbi_export');
            }
            return $resultRedirect->setPath('powerbi_export');
        } else {
            $this->messageManager->addErrorMessage(__('Export functionality is currently disabled'));
            return $resultRedirect->setPath('powerbi_export');
        }
    }
}
