<?php

namespace Abbott\DatabaseBackup\Console\Command;

use Abbott\DatabaseBackup\Helper\Backup;
use Abbott\DatabaseBackup\Logger\Method\Logger;
use Abbott\DatabaseBackup\Model\DBBackup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Dbdump extends Command
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
     * @var Backup
     */
    private $backupHelper;

    /**
     * Constructor
     *
     * @param Logger $logger
     * @param DBBackup $dbbackup
     * @param Backup $backupHelper
     */
    public function __construct(Logger $logger, DBBackup $dbbackup, Backup $backupHelper)
    {
        $this->logger = $logger;
        $this->dbbackup = $dbbackup;
        $this->backupHelper = $backupHelper;
        parent::__construct();
    }

    /**
     * Configure function
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('db:dump-to-s3');
        $this->setDescription('Command to dump db and store it in s3.');
        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('<comment>Db dump started.</comment>');
        $this->logger->info("DB Backup Cronjob has started at " . date("mm-dd-yyyy H:i:s"));
        try {
            if ($this->backupHelper->getDbBackupConfig(Backup::ENABLE)) {
                $this->dbbackup->execute();
            } else {
                $output->writeln('<comment>Db dump functionality is disabled from configuration.</comment>');
            }
        } catch (\Exception $ex) {
            $this->logger->critical(" Exception while executing cron job " . $ex->getMessage());
        }
        $this->logger->info("DB Backup Cronjob has ended at " . date("mm-dd-yyyy H:i:s"));
        $output->writeln('<info>Db dump succeeded.</info>');
    }
}
