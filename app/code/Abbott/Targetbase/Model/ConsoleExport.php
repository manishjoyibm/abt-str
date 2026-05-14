<?php
namespace Abbott\Targetbase\Model;

use Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

class ConsoleExport extends Command
{
    public $exportorderdata;
    public $state;
    public function __construct(
        \Abbott\Targetbase\Model\Exportorderdata $exportorderdata,
        \Magento\Framework\App\State $state
    ) {
        $this->exportorderdata = $exportorderdata;
        $this->state = $state;
        parent::__construct();
    }

    protected function configure()
    {
        $options = [
            new InputOption(
                'fromDate',
                null,
                InputOption::VALUE_REQUIRED,
                'From Date'
            ),
            new InputOption(
                'toDate',
                null,
                InputOption::VALUE_REQUIRED,
                'To Date'
            ),
        ];
        $this->setName('targetbase:orderexport')
            ->setDescription('Targetbase Order Export');
        $this->setDefinition($options);
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        if ($input->getOption('fromDate') && $input->getOption('toDate')) {
            $message = $this->exportorderdata->exportOrderDataWithDate(
                $input->getOption('fromDate'),
                $input->getOption('toDate')
            );
            echo $message;
        }
    }
}
