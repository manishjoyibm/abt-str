<?php
namespace Abbott\Targetbase\Model;

use Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

class CustomerExport extends Command
{
    public $exportdata;
    public $state;
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    public $cacheTypeList;
    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    public $cacheFrontendPool;
    public $syncdata;
    public function __construct(
        \Abbott\Targetbase\Model\Exportdata $exportdata,
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Abbott\Targetbase\Model\Syncdata $syncdata
    ) {
        $this->exportdata = $exportdata;
        $this->state = $state;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->syncdata = $syncdata;
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
        $this->setName('targetbase:customerexport')
            ->setDescription('Targetbase Customer Export');
        $this->setDefinition($options);
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        if ($input->getOption('fromDate') && $input->getOption('toDate')) {
            $fileName = $this->exportdata->exportCustomerDataWithDate(
                $input->getOption('fromDate'),
                $input->getOption('toDate')
            );

            $this->syncdata->syncCustomerFile($fileName);

            echo $fileName . PHP_EOL ;
        }
    }
}
