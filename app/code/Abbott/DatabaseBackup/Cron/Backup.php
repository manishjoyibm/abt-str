<?php

namespace Abbott\DatabaseBackup\Cron;

use Abbott\DatabaseBackup\Logger\Method\Logger;
use Abbott\DatabaseBackup\Model\DBBackup;
use Abbott\DatabaseBackup\Helper\Backup as BackupHelper;

class Backup
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var DBBackup
     */
    protected $dbbackup;

    /**
     * @var BackupHelper
     */
    private $backupHelper;

    /**
     * Constructor
     * @param Logger $logger
     * @param DBBackup $dbbackup
     * @param BackupHelper $backupHelper
     */
    public function __construct(Logger $logger, DBBackup $dbbackup, BackupHelper $backupHelper)
    {
        $this->logger = $logger;
        $this->dbbackup = $dbbackup;
        $this->backupHelper = $backupHelper;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute(): void
    {
        $this->logger->info("DB Backup Cronjob has started at " . date("m-d-y H:i:s"));
        try {
            if ($this->backupHelper->getDbBackupConfig(BackupHelper::ENABLE)) {
                $this->dbbackup->execute();
            } else {
                $this->logger->info("DB Backup Cronjob is disabled, Please enable it from configuration");
            }
        } catch (\Exception $ex) {
            $this->logger->critical(" Exception while executing cron job " . $ex->getMessage());
        }
        $this->logger->info("DB Backup Cronjob has ended at " . date("m-d-y H:i:s"));
    }
}
