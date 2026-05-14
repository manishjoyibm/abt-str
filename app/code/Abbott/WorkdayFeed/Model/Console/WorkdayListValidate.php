<?php

namespace Abbott\WorkdayFeed\Model\Console;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\State;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\DirectoryList;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Abbott\WorkdayFeed\Helper\InboundFeedHelper;

class WorkdayListValidate extends Command
{
    /**
     * @var DirectoryList
     */
    protected DirectoryList $dir;

    /**
     * @var GroupRepositoryInterface
     */
    protected GroupRepositoryInterface $groupRepository;

    /**
     * @var State
     */
    protected State $state;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $customerFactory;

    /**
     * @var Csv
     */
    protected Csv $csvProcessor;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    public const NAME_CSV_FILE = "file";

    /**
     * @param DirectoryList $dir
     * @param GroupRepositoryInterface $groupRepository
     * @param State $state
     * @param CustomerFactory $customerFactory
     * @param Csv $csvProcessor
     * @param LoggerInterface $logger
     */
    public function __construct(
        DirectoryList $dir,
        GroupRepositoryInterface $groupRepository,
        State $state,
        CustomerFactory $customerFactory,
        Csv $csvProcessor,
        LoggerInterface $logger
    ) {
        $this->dir = $dir;
        $this->groupRepository = $groupRepository;
        $this->state = $state;
        $this->customerFactory = $customerFactory;
        $this->csvProcessor = $csvProcessor;
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * Method configure
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName("validate-workday-list");
        $this->setDescription("Workday employee validate");
        $this->setDefinition([
            new InputOption(self::NAME_CSV_FILE, "-c", InputOption::VALUE_REQUIRED, "CSV File"),
        ]);
        parent::configure();
    }

    /**
     * Method execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws FileSystemException
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $fileName = $input->getOption(self::NAME_CSV_FILE);
        $filepath = $this->dir->getPath('var') . '/' . $fileName;
        $customerGroupArray = ['employee', 'retiree', 'abbvie'];
        $fileData[] = ['Id', 'Name', 'Email', 'CustomerGroup'];
        if (file_exists($filepath)) {
            $lines = file($filepath);
            if ($this->fileValidator($lines[0])) {
                unset($lines[0]);
                $i = 1;
                foreach ($lines as $line) {
                    echo $i." ";
                    try {
                        $words = preg_replace("/[\r\n]/", "", explode("|", $line));
                        $customerEmail = !empty($words[7]) ? $words[7] : null;
                        if ($customerEmail) {
                            $customer = $this->customerFactory->create()->getCollection()
                                ->addAttributeToFilter("email", ["eq" => $customerEmail])->load();
                            if ($customer->getSize() > 0) {

                                $customer = $customer->getFirstItem();

                                $group = $this->groupRepository->getById($customer->getData('group_id'));
                                $groupName = strtolower($group->getCode());
                                if (!in_array($groupName, $customerGroupArray)) {
                                    $filepathWrongGroup = $this->dir->getPath('var') . '/export/workday_wrong_group' .
                                        '.csv';
                                    $fileDataWrongGroup[] = [
                                        $customer->getData('group_id'),
                                        $customer->getData('firstname'),
                                        $customer->getData('email'), $group->getCode()
                                    ];

                                    $this->csvProcessor->setDelimiter(',')
                                        ->setEnclosure('"')->saveData(
                                            $filepathWrongGroup,
                                            $fileDataWrongGroup
                                        );
                                }
                            } else {
                                $filepath = $this->dir->getPath('var') . '/export/workday_record_not_exists' . '.csv';
                                $fileData[] = ['NA', $words[4], $customerEmail, 'NA'];
                                $this->csvProcessor->setDelimiter(',')
                                    ->setEnclosure('"')->saveData(
                                        $filepath,
                                        $fileData
                                    );
                            }
                        }
                    } catch (\Exception $e) {
                        $this->logger->info("Exception with Email: $customerEmail  " . $e->getMessage());
                    }
                    $i++;
                }
            } else {
                $this->logger->info("Invalid file/columns");
            }
        } else {
            $this->logger->info("File not exists");
        }
    }

    /**
     * Method fileValidator
     *
     * @param $header
     * @return bool
     */
    private function fileValidator($header): bool
    {
        $columIds = [
            InboundFeedHelper::COLUMN_ONE_NAME,
            InboundFeedHelper::COLUMN_TWO_NAME,
            InboundFeedHelper::COLUMN_THREE_NAME,
            InboundFeedHelper::COLUMN_FOUR_NAME,
            InboundFeedHelper::COLUMN_FIVE_NAME,
            InboundFeedHelper::COLUMN_SIX_NAME,
            InboundFeedHelper::COLUMN_SEVEN_NAME,
            InboundFeedHelper::COLUMN_EIGHT_NAME,
        ];
        $columNames = preg_replace(
            "/[\r\n]/",
            "",
            explode("|", $header)
        );
        return $columNames == $columIds;
    }
}
