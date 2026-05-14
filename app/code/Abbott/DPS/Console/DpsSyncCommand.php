<?php

namespace Abbott\DPS\Console;

use Abbott\DPS\Cron\DpsListSync;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DpsSyncCommand extends Command
{
    /**
     * @var DpsListSync
     */
    protected DpsListSync $dpsListSync;

    /**
     * Test constructor.
     * @param DpsListSync $dpsListSync
     * @param string|null $name
     */
    public function __construct(DpsListSync $dpsListSync, string $name = null)
    {
        $this->dpsListSync = $dpsListSync;
        parent::__construct($name);
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('abbott:dpc-sync');
        $this->setDescription('Sync DPS list from gov website');
        parent::configure();
    }

    /**
     * Method Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->dpsListSync->execute();
    }
}
