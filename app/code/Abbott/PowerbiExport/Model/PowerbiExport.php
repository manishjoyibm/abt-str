<?php

namespace Abbott\PowerbiExport\Model;

use Abbott\PowerbiExport\Model\Powerbi as PowerbiFactory;
use Abbott\PowerbiExport\Helper\Powerbi;
use Abbott\PowerbiExport\Logger\Method\Logger;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;


class PowerbiExport
{
    protected $timezoneInterface;

    /**
     * @var PowerbiFactory
     */
    protected $powerbiModelFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Powerbi
     */
    private $powerbiHelper;

    /**
     * @var varDirectory
     */
    private $varDirectory;


    protected $file;


    public function __construct(
        PowerbiFactory $powerbiModelFactory,
        Logger $logger,
        Powerbi $powerbiHelper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Driver\File $file,
        TimezoneInterface $timezoneInterface
        )
    {
        $this->powerbiModelFactory = $powerbiModelFactory;
        $this->logger = $logger;
        $this->powerbiHelper = $powerbiHelper;
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->file = $file;
        $this->timezoneInterface = $timezoneInterface;
    }

    /** Function which get and store CSV respose to SFTP
     * @return $this
     * @throws LocalizedException
     */
    public function execute()
    {
        $collections = $this->powerbiModelFactory->getCollection();
        $collections = $collections->addFieldToFilter('status', true);

        foreach ($collections as $collection) {

            $response = $this->powerbiHelper->getReportResponse($collection->getReportId());
            $response = $response ? $response : ' ';

            if (ctype_space($response)) {
                continue;
            }

            try {
                $this->logger->info(sprintf('Export copy started'));
                
                $dumpFileName = str_replace(" ", "_", sprintf($collection->getReportName().'_%s.csv', date('d_m_y')));
                $varDirectory = $this->varDirectory->getAbsolutePath().Powerbi::MBI_PATH.'/';
                $dumpFile = $varDirectory . $dumpFileName;

                $fileopen = $this->file->fileOpen($dumpFile, 'w');
                fputs($fileopen, $response);
                
                $this->logger->info("Report ID: ".$collection->getReportId()." export done");
                
                $dateTime = $this->timezoneInterface->date()->format('Y-m-d H:i:s');
                
                $collection->setData('last_cron_update_date', $dateTime);
                
                $collection->save();

            }catch (LocalizedException $e) {
                return $this->logger->critical("Exception while executing command " . $e->getMessage());
            }
        }
    }



}
