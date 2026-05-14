<?php

namespace Abbott\WorkdayFeed\Model;

use Abbott\WorkdayFeed\Model\ResourceModel\InboundFeed\CollectionFactory;
use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\ScopeInterface;
use Abbott\WorkdayFeed\Helper\InboundFeedHelper;
use Abbott\MyAccount\Helper\Data as AccountHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Math\Random;
use Magento\Store\Model\StoreManagerInterface;
use phpseclib3\Net\SFTP;
use Psr\Log\LoggerInterface;

class WorkdaySync extends AbstractModel
{
    public $directory;
    public $scopeConfig;
    public $transportBuilder;
    public $file;
    public $date;
    public $jsonSerializer;
    public $wdDecryptor;
    public $addCounter;
    public $modifiedCounter;
    public $deleteCounter;
    public $failCounter;
    const REMOTE_TIMEOUT = 30;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var ResponseFactory
     */
    protected ResponseFactory $responseFactory;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var InboundFeedFactory
     */
    protected InboundFeedFactory $inboundFeedFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    protected ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $customerFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $customerRepository;

    /**
     * @var Collection
     */
    protected Collection $customerCollection;

    /**
     * @var ResourceConnection
     */
    protected ResourceConnection $resource;

    protected $directoryList;

    /**
     * @var InboundFeedHelper
     */
    protected InboundFeedHelper $helper;

    /**
     * @var ResourceModel\InboundFeed\CollectionFactory
     */
    protected ResourceModel\InboundFeed\CollectionFactory $inboundFeedCollectionFactory;

    /**
     * @var CustomerRegistry
     */
    private CustomerRegistry $customerRegistry;

    private $accountManagement;

    /**
     * @var Random
     */
    private Random $mathRandom;

    public const WD_CUSTOMR_EMAIL = 'email';

    /**
     * Constructor
     *
     * @param StoreManagerInterface $storeManager
     * @param ResponseFactory $responseFactory
     * @param LoggerInterface $logger
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param Collection $customerCollection
     * @param InboundFeedFactory $inboundFeedFactory
     * @param ResourceConnection $resource
     * @param DirectoryList $directoryList
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param InboundFeedHelper $helper
     * @param WorkdayDecryption $wdDecryptor
     * @param File $file
     * @param DateTime $date
     * @param Json $jsonSerializer
     * @param CustomerRegistry $customerRegistry
     * @param AccountManagement $accountManagement
     * @param Random $mathRandom
     * @param ResourceModel\InboundFeed\CollectionFactory $inboundFeedCollectionFactory
     */
    public function __construct(
        StoreManagerInterface                              $storeManager,
        ResponseFactory                                    $responseFactory,
        LoggerInterface                                                       $logger,
        ModuleDataSetupInterface                                              $moduleDataSetup,
        CustomerFactory                                                       $customerFactory,
        CustomerRepositoryInterface                                           $customerRepository,
        Collection                                                            $customerCollection,
        InboundFeedFactory                                                    $inboundFeedFactory,
        ResourceConnection                                                    $resource,
        DirectoryList                                                         $directoryList,
        ScopeConfigInterface                                                  $scopeConfig,
        TransportBuilder                                                      $transportBuilder,
        InboundFeedHelper                                                     $helper,
        WorkdayDecryption                                                     $wdDecryptor,
        File                             $file,
        DateTime                           $date,
        Json                          $jsonSerializer,
        CustomerRegistry                                                      $customerRegistry,
        AccountManagement                                                     $accountManagement,
        Random                                                                $mathRandom,
        CollectionFactory $inboundFeedCollectionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->customerCollection = $customerCollection;
        $this->inboundFeedFactory = $inboundFeedFactory;
        $this->resource = $resource;
        $this->directory = $directoryList;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->helper = $helper;
        $this->file = $file;
        $this->date = $date;
        $this->jsonSerializer = $jsonSerializer;
        $this->wdDecryptor = $wdDecryptor;
        $this->customerRegistry = $customerRegistry;
        $this->accountManagement = $accountManagement;
        $this->mathRandom = $mathRandom;
        $this->addCounter = 0;
        $this->modifiedCounter = 0;
        $this->deleteCounter = 0;
        $this->failCounter = 0;
        $this->inboundFeedCollectionFactory = $inboundFeedCollectionFactory;
    }

    /**
     * Method Execute
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute(): void
    {
        if (!$this->scopeConfig->getValue(
            'workday_feed_settings/workday_crons/es_enabled',
            ScopeInterface::SCOPE_STORE
        )) {
            return;
        }
        $this->logger->info('Employee Sync Cron starts');
        $this->sFTPReader();
    }

    /**
     * @return void
     */
    public function sFTPReader(): void
    {
        $companys = [
            $this->helper->getAbbottSFTPPath(),
            $this->helper->getAbbvieSFTPPath(),
            $this->helper->getAlereSFTPPath()
            ];
        $ssh = new SFTP($this->helper->getHost(), $this->helper->getPort(), self::REMOTE_TIMEOUT);
        if (!$ssh->login($this->helper->getUserName(), $this->helper->getPassword())) {
            throw new LocalizedException(
                new Phrase(sprintf(
                    "Unable to open SFTP connection as %s@%s",
                    $this->helper->getUserName(),
                    $this->helper->getHost()
                ))
            );
        }
        foreach ($companys as $compIndex => $company) {
            try {
                $ssh->chdir($company);
                $getPath = $ssh->pwd();
                $result = $ssh->nlist($ssh->pwd());
                foreach ($result as $filename) {
                    if (stripos($filename, '.txt') !== false) {
                        $targetFn = $this->date->timestamp().'_'.$filename;
                        $ssh->get(
                            $getPath.'/'.$filename,
                            $this->directory->getPath('var')
                            .InboundFeedHelper::FILE_PATH.$targetFn
                        );
                        $this->logger->info('Employee Sync Cron starts');
                        $inboundFeed = $this->inboundFeedFactory->create();
                        $inboundFeed->setFileName($targetFn);
                        $inboundFeed->setStatus(InboundFeedHelper::STATUS_DECRYPTING);
                        $inboundFeed->setType(InboundFeedHelper::FILE_CONTENT_TYPE);
                        $inboundFeed->save();
                        $ssh->put(
                            $this->helper->getArchiveSFTPPath().$filename,
                            $this->file->fileGetContents($this->directory->getPath(
                                'var'
                            ).InboundFeedHelper::FILE_PATH.$targetFn)
                        );
                        $ssh->delete($getPath.'/'.$filename);
                        $this->wdDecryptor->decryptWorkdayFile(
                            $inboundFeed,
                            $this->directory->getPath('var')
                            .InboundFeedHelper::FILE_PATH.$targetFn,
                            $compIndex
                        );
                    }
                }
            } catch (Exception $ex) {
                $this->logger->critical($ex);
                if ($inboundFeed) {
                    $inboundFeed->setStatus(InboundFeedHelper::STATUS_FAILED);
                    $inboundFeed->save();
                }
            }
        }
    }

    public function processFeed()
    {
        if (!$this->scopeConfig->getValue(
            'workday_feed_settings/workday_crons/es_enabled',
            ScopeInterface::SCOPE_STORE
        )) {
            return;
        }
        $this->logger->info('Employee Process Cron starts');
        /** @var ResourceModel\InboundFeed\Collection $inboundFeedCollection */
        $inboundFeedCollection = $this->inboundFeedCollectionFactory->create();
        $inboundFeedCollection
            ->addFieldToFilter('type', InboundFeedHelper::FILE_CONTENT_TYPE)
            ->addFieldToFilter('status', InboundFeedHelper::STATUS_DECRYPTING);
        if ($inboundFeedCollection->getSize() > 0) {
            foreach ($inboundFeedCollection as $inboundFeed) {
                try {
                    $this->gridReader($inboundFeed->getId());
                } catch (Exception $e) {
                    $inboundFeed->setMessage($e->getMessage());
                    $inboundFeed->setStatus(InboundFeedHelper::STATUS_FAILED);
                    $inboundFeed->save();
                    $this->logger->critical($e);
                }
            }
        }
    }

    /**
     * Method gridReader
     *
     * @param int $feedId
     */
    public function gridReader(int $feedId): void
    {
        $this->failCounter = 0;
        $this->addCounter = 0;
        $this->modifiedCounter = 0;
        $this->deleteCounter = 0;
        $inboundFeed = $this->inboundFeedFactory->create()->load($feedId);
        $path = $this->directory->getPath('var').InboundFeedHelper::FILE_PATH.$inboundFeed->getFileName();
        if (!file_exists($path)) {
            throw new NotFoundException(__("File is not found"));
        }
        $lines = file($path);
        $inboundFeed->setStatus(InboundFeedHelper::STATUS_PENDING);
        $inboundFeed->setMessage(InboundFeedHelper::MESSAGE_PENDING);
        $inboundFeed->setType(InboundFeedHelper::FILE_CONTENT_TYPE);
        $inboundFeed->save();
        $websiteId = (int)$this->storeManager->getStore(AccountHelper::ABT_STORE_ID)->getWebsiteId();
        try {
            if ($this->fileValidator($lines[0])) {
                foreach ($lines as $line) {
                    $this->processInboundFile($line, $inboundFeed->getFeedId(), $websiteId);
                }
                $success = $this->addCounter + $this->modifiedCounter + $this->deleteCounter;
                $message = ["Total Records" => (count($lines) - 1), "Success" => $success,
                    "Failed" => $this->failCounter,"Added" => $this->addCounter, "Modified"
                    => $this->modifiedCounter, "Shifted" => $this->deleteCounter];
                $inboundFeed->setMessage($this->jsonSerializer->serialize($message));
                $inboundFeed->setStatus(InboundFeedHelper::STATUS_PROCESED);
                $inboundFeed->save();
                $this->getSendEmail($inboundFeed->getFileName(), $inboundFeed->getCreatedAt(), count($lines));
            } else {
                $inboundFeed->setMessage($this->jsonSerializer->serialize(InboundFeedHelper::INVALID_COLUMN_ORDERS));
                $inboundFeed->setStatus(InboundFeedHelper::STATUS_FAILED);
                $inboundFeed->save();
            }
            $this->addCounter = 0;
            $this->modifiedCounter = 0;
            $this->deleteCounter = 0;
            $this->failCounter = 0;

        } catch (Exception $ex) {
            $inboundFeed->setStatus(InboundFeedHelper::STATUS_FAILED);
            $inboundFeed->save();
            $this->getSendEmail($inboundFeed->getFileName(), $inboundFeed->getCreatedAt(), count($lines));
            $this->logger->critical($ex);
        }
    }

    /**
     * Method getSendEmail
     *
     * @param string $fileName
     * @param string $createdAt
     * @param int $total
     */
    private function getSendEmail(string $fileName, string $createdAt, int $total): void
    {
        if ($this->helper->isEnabled()) {
            $mails = $this->helper->getToMails();

            if (isset($mails) && !empty($mails)) {
                $mails = explode(",", $mails);
                $storeId = AccountHelper::ABT_STORE_ID;
                $this->sendEmailTemplate(
                    null,
                    InboundFeedHelper::EMAIL_TEMPLATE,
                    InboundFeedHelper::XML_PATH_EMAIL_SENDER,
                    $this->emailTemplateData($fileName, $createdAt, $total),
                    $storeId,
                    $mails
                );
            }
        }
    }

    /**
     * File fileValidator
     *
     * @param string $header
     * @return bool
     */
    private function fileValidator(string $header): bool
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
        $columNames = preg_replace("/[\r\n]/", "", explode("|", $header));
        return $columNames == $columIds;
    }

    /**
     * Method processInboundFile
     *
     * @param string $record
     * @param int $feedId
     * @param int $websiteId
     */
    private function processInboundFile(string $record, int $feedId, int $websiteId): void
    {
        $words = preg_replace("/[\r\n]/", "", explode("|", $record));
        if ($words[0] === InboundFeedHelper::ADD) {
            $this->addUpdateProcess($words, $feedId, $websiteId);
        } elseif ($words[0] === InboundFeedHelper::MODIFY) {
            $this->addUpdateProcess($words, $feedId, $websiteId);
        } elseif ($words[0] === InboundFeedHelper::DELETE) {
            $this->deleteProcess($words, $feedId, $websiteId);
        } else {
            $this->noRecordStatus($words, $feedId, $websiteId);
        }
    }

    /**
     * Method addUpdateProcess
     *
     * @param array $words
     * @param int $feedId
     * @param int $websiteId
     */
    private function addUpdateProcess(array $words, int $feedId, $websiteId): void
    {
        try {
            $customer = $this->customerFactory->create();

            $upiOnly = $words[2];
            $words[2] = $words[6].$words[2];
            $collection = $customer->getCollection()->addAttributeToFilter(
                InboundFeedHelper::WD_UPI,
                $words[2]
            )->addAttributeToFilter(
                "website_id",
                $websiteId
            );

            if (!$collection->getSize()) {
                $collection = $customer->getCollection()->addAttributeToFilter(
                    InboundFeedHelper::WD_UPI,
                    $upiOnly
                )->addAttributeToFilter(
                    "website_id",
                    $websiteId
                );
            }

            //verify with Email, if record exists then update Compnay, Group, and UPI
            $customerEmail = !empty($words[7]) ? $words[7] : null;
            if (!$collection->getSize() && $customerEmail) {
                $collection = $customer->getCollection()->addAttributeToFilter(
                    self::WD_CUSTOMR_EMAIL,
                    $customerEmail
                )->addAttributeToFilter(
                    "website_id",
                    $websiteId
                );
            }

            if ($collection->getSize() > 0) {
                $this->updateProcess($words, $feedId, $collection);
            } else {

                $customer->setWebsiteId($websiteId);
                $customer->setStoreId(AccountHelper::ABT_STORE_ID);
                $customer->setLastname(str_replace(" ", "", $words[3]));
                $customer->setFirstname($words[4]);
                $words[7] = empty($words[7]) ? $this->genEmail($words) : $words[7];
                $customer->setEmail($words[7]);
                if ($words[6] == InboundFeedHelper::ABBOTT_COMPANY_NAME) {
                    $customer->setGroupId($this->helper->getAbbottEmployeeGroupId());
                } elseif ($words[6] == InboundFeedHelper::ABBVIE_COMPANY_NAME) {
                    $customer->setGroupId($this->helper->getAbbvieEmployeeGroupId());
                } elseif ($words[6] == InboundFeedHelper::ALERE_COMPANY_NAME) {
                    $customer->setGroupId($this->helper->getAlereEmployeeGroupId());
                }
                $customer->save();
                $customerRepo = $this->customerRepository->get($words[7], $websiteId);
                $customerRepo->setCustomAttribute(InboundFeedHelper::WD_STATUS, $words[1]);
                $customerRepo->setCustomAttribute(InboundFeedHelper::WD_UPI, $words[2]);
                $customerRepo->setCustomAttribute(InboundFeedHelper::WD_COMPANY, $words[6]);
                $this->customerRepository->save($customerRepo);

                /*
                * Generate reset token for customer and send it in email
                */

                $newLinkToken = $this->mathRandom->getUniqueHash();
                $this->accountManagement->changeResetPasswordLinkToken($customerRepo, $newLinkToken);
                $customerData = $this->customerRegistry->retrieveSecureData($customer->getId());

                $storeId = AccountHelper::ABT_STORE_ID;

                if ($this->helper->customerEmailEnabled()) {
                    $this->sendEmailTemplate(
                        $customer,
                        InboundFeedHelper::WORKDAY_CUSTOMER_EMAIL_TEMPLATE,
                        AccountManagement::XML_PATH_FORGOT_EMAIL_IDENTITY,
                        ['customer' => $customerData, 'store' => $this->storeManager->getStore($storeId)],
                        $storeId
                    );
                }


                $this->addCounter ++;
                $this->idxTable(
                    $words[0],
                    $words[2],
                    $words,
                    InboundFeedHelper::SUCCESS_STATUS,
                    InboundFeedHelper::NO_EXCEPTION,
                    $feedId
                );
            }
        } catch (Exception $ex) {
            $this->failLogger($words, $ex->getMessage(), $feedId);
        }
    }

    /*
    * Send email template
    */
    protected function sendEmailTemplate(
        $customer,
        $template,
        $sender,
        $templateParams = [],
        $storeId = null,
        $email = null
    ) {
        $templateId = $this->scopeConfig->getValue(
            $template,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($email === null) {
            $email = $customer->getEmail();
        }

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId
                ]
            )
            ->setTemplateVars($templateParams)
            ->setFrom(
                $this->scopeConfig->getValue(
                    $sender,
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                )
            )
            ->addTo($email)
            ->getTransport();

        $transport->sendMessage();

        return $this;
    }

    /**
     * Method updateProcess
     *
     * @param array $words
     * @param int $feedId
     * @param int $websiteId
     */
    private function updateProcess(array $words, int $feedId, $collection = null)
    {
        try {

            $collection = $collection->getFirstItem();
            if ($collection) {
                $customerRepo = $this->customerRepository->getById($collection->getEntityId());
                $customerRepo->setCustomAttribute(InboundFeedHelper::WD_STATUS, $words[1]);
                $customerRepo->setCustomAttribute(InboundFeedHelper::WD_COMPANY, $words[6]);
                $customerRepo->setCustomAttribute(InboundFeedHelper::WD_UPI, $words[2]);
                $customerRepo->setLastname(str_replace(" ", "", $words[3]));
                $customerRepo->setFirstname($words[4]);
                $customerRepo->setEmail($words[7]);

                /*
                * Chech status of customer whether it is Retiree or Active in workday
                * Check customer company and assign to to a specific group
                * Change the customer group accordingly
                */

                if ($words[6] == InboundFeedHelper::ABBOTT_COMPANY_NAME) {
                    if ($words[1] == InboundFeedHelper::RETIREE) {
                        $customerRepo->setGroupId($this->helper->getAbbottRetireeGroupId());
                    } else {
                        $customerRepo->setGroupId($this->helper->getAbbottEmployeeGroupId());
                    }
                } elseif ($words[6] == InboundFeedHelper::ABBVIE_COMPANY_NAME) {
                    $customerRepo->setGroupId($this->helper->getAbbvieEmployeeGroupId());
                } elseif ($words[6] == InboundFeedHelper::ALERE_COMPANY_NAME) {
                    $customerRepo->setGroupId($this->helper->getAlereEmployeeGroupId());
                }

                $this->customerRepository->save($customerRepo);
            } else {
                $this->logger->critical($words[2]." UPI is not found");
            }
            $this->modifiedCounter ++;
            $this->idxTable(
                $words[0],
                $words[2],
                $words,
                InboundFeedHelper::SUCCESS_STATUS,
                InboundFeedHelper::NO_EXCEPTION,
                $feedId
            );
        } catch (Exception $ex) {
            $this->failLogger($words, $ex->getMessage(), $feedId);
        }
    }

    /**
     * Method deleteProcess
     *
     * @param array $words
     * @param int $feedId
     * @param int $websiteId
     */
    private function deleteProcess(array $words, int $feedId, int $websiteId): void
    {
        try {
            $customerRepo = $this->customerRepository->get($words[7], $websiteId);
            $customerRepo->setGroupId(InboundFeedHelper::CONSUMER_GROUP_ID);
            $customerRepo->setCustomAttribute(InboundFeedHelper::WD_STATUS, "");
            $customerRepo->setCustomAttribute(InboundFeedHelper::WD_UPI, "");
            $customerRepo->setCustomAttribute(InboundFeedHelper::WD_COMPANY, "");
            $this->customerRepository->save($customerRepo);
            $this->deleteCounter ++;
            $this->idxTable(
                $words[0],
                $words[2],
                $words,
                InboundFeedHelper::SUCCESS_STATUS,
                InboundFeedHelper::NO_EXCEPTION,
                $feedId
            );
        } catch (Exception $ex) {
            $this->failLogger($words, $ex->getMessage(), $feedId);
        }
    }

    /**
     * Method genEmail
     *
     * @param array $words
     * @return string
     */
    private function genEmail(array $words): string
    {
        return strtolower($words[4].str_replace(" ", "", $words[3])."@noreply.".$words[6].".com");
    }

    /**
     * Method noRecordStatus
     *
     * @param array $words
     * @param int $feedId
     */
    private function noRecordStatus(array $words, int $feedId): void
    {
        if ($words[0] !== InboundFeedHelper::COLUMN_ONE_NAME) {
            try {
                $this->idxTable(
                    $words[0],
                    $words[2],
                    $words,
                    InboundFeedHelper::FAILURE_STATUS,
                    InboundFeedHelper::UNKNOWN_RECORD,
                    $feedId
                );
                $this->failCounter ++;
            } catch (Exception $ex) {
                $this->failCounter ++;
                $this->idxTable(
                    '',
                    '',
                    $words,
                    InboundFeedHelper::FAILURE_STATUS,
                    InboundFeedHelper::EMPTY_LINE,
                    $feedId
                );
                $this->logger->critical($ex);
            }
        }
    }

    /**
     * Method failLogger
     *
     * @param array $words
     * @param string $message
     * @param int $feedId
     */
    private function failLogger(array $words, string $message, int $feedId): void
    {
        $this->failCounter ++;
        $this->idxTable($words[0], $words[2], $words, InboundFeedHelper::FAILURE_STATUS, $message, $feedId);
        $this->logger->critical($message);
    }

    /**
     * Method idxTable
     *
     * @param string $recordStatus
     * @param string $upi
     * @param array $words
     * @param string $status
     * @param string $exception
     * @param int $feedId
     */
    private function idxTable(
        string $recordStatus,
        string $upi,
        array $words,
        string $status,
        string $exception,
        int $feedId
    ): void
    {
        $this->moduleDataSetup->getConnection()->insertForce(
            $this->moduleDataSetup->getTable(InboundFeedHelper::WORKDAY_IDX_TABLE),
            [ InboundFeedHelper::IDX_TABLE_COLUMN_ONE => $recordStatus,
                InboundFeedHelper::IDX_TABLE_COLUMN_TWO => $upi,
                InboundFeedHelper::IDX_TABLE_COLUMN_THREE => $this->jsonSerializer->serialize($words),
                InboundFeedHelper::IDX_TABLE_COLUMN_FOUR => $status,
                InboundFeedHelper::IDX_TABLE_COLUMN_FIVE => $exception,
                InboundFeedHelper::IDX_TABLE_COLUMN_SIX => $feedId
              ]
        );
    }

    /**
     * @return void
     */
    public function deleteWorkdayFeedData()
    {
        if (!$this->scopeConfig->getValue(
            'workday_feed_settings/workday_crons/dl_enabled',
            ScopeInterface::SCOPE_STORE
        )) {
            return;
        }
        $lifetimeIdx = $this->helper->lifeTimeIdx();
        $lifetimeFeed = $this->helper->lifeTimeFeed();
        try {
            $connection = $this->resource->getConnection();
            $connection->delete(
                InboundFeedHelper::WORKDAY_IDX_TABLE,
                "created_at < date_sub(CURDATE(),INTERVAL " .$lifetimeIdx."  Day)"
            );
            $connection->delete(
                InboundFeedHelper::INBOUND_FEED_TABLE,
                "created_at < date_sub(CURDATE(),INTERVAL " .$lifetimeFeed."  Day) and
                status IN ('"
                .InboundFeedHelper::STATUS_PROCESED."', '".InboundFeedHelper::SUCCESS_STATUS."')"
            );
        } catch (Exception $ex) {
            $this->logger->critical($ex);
        }
    }

    /**
     * Method emailTemplateData
     *
     * @param string $fileName
     * @param string $createdAt
     * @param int $total
     * @return array
     */
    private function emailTemplateData(string $fileName, string $createdAt, int $total): array
    {
        return [
            'file_name' => $fileName,
            'creation_time' => $createdAt,
            'total_records' => $total-1,
            'added' => $this->addCounter,
            'modifed' => $this->modifiedCounter,
            'shifted' => $this->deleteCounter,
            'failed' => $this->failCounter,
        ];
    }
}
