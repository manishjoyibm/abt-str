<?php
namespace Abbott\PowerbiExport\Console\Command;

use Abbott\PowerbiExport\Helper\Powerbi as PowerbiHelper;
use Abbott\PowerbiExport\Logger\Method\Logger;
use Abbott\PowerbiExport\Model\PowerbiExport as Powerbi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PowerbiExport
 */
class PowerbiExport extends Command
{
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var Powerbi
     */
    protected $powerbi;
    /**
     * @var PowerbiHelper
     */
    protected $powerbiHelper;

    /**
     * Constructor
     * @param Logger $logger
     * @param Powerbi $powerbi
     */
    public function __construct(
        Logger $logger,
        Powerbi $powerbi,
        PowerbiHelper $powerbiHelper
    )
    {
        $this->logger = $logger;
        $this->powerbi = $powerbi;
        $this->powerbiHelper = $powerbiHelper;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('report:mbi-to-powerbi');
        $this->setDescription('Command to export MBI report to powerbi');
        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Export started.</comment>');
        $this->logger->info("Export API Job has started at ".date("m-d-y H:i:s"));
        try {
            if ($this->powerbiHelper->getPowerbiConfig(PowerbiHelper::ENABLE_POWERBI_EXPORT)) {
                $this->powerbi->execute();
            } else {
                $output->writeln(
                    '<comment>PowerBI report export functionality is disabled.</comment>'
                );
            }
        } catch (\Exception $ex) {
            $this->logger->critical(" Exception while executing job ". $ex->getMessage());
        }
        $this->logger->info("Export API Job has ended at ".date("m-d-y H:i:s"));
        $output->writeln('<info>Export API job End.</info>');
    }
}
