<?php
namespace Abbott\PowerbiExport\Helper;

use Abbott\PowerbiExport\Logger\Method\Logger;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Abbott\PowerbiExport\Api\PowerbiExportRepositoryInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Client\CurlFactory;

class Powerbi extends AbstractHelper
{
    public const ENABLE_POWERBI_EXPORT = "powerbi_export/powerbi/enable";
    public const ENABLE_CRON_POWERBI_EXPORT = "powerbi_export/powerbi/enable_cron";
    public const MBI_EXPORT_API_KEY = "powerbi_export/powerbi/mbi_export_api_key";
    public const MBI_EXPORT_API_URL = "powerbi_export/powerbi/mbi_export_api_url";
    public const MBI_EXPORT_DELETE_FILE_CONFIG = "powerbi_export/powerbi/mbi_export_delete_file_setting";
    public const MBI_PATH = "mbi";

    /**
     *
     * @var CurlFactory
     */
    protected CurlFactory $curlFactory;

    /**
     *
     * @var searchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     *
     * @var filterBuilder
     */
    protected FilterBuilder $filterBuilder;

    /**
     *
     * @var filterGroupBuilder
     */
    protected FilterGroupBuilder $filterGroupBuilder;

    /**
     *
     * @var PowerbiExportRepositoryInterface
     */
    protected PowerbiExportRepositoryInterface $powerbiRepository;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @var EncryptorInterface
     */
    protected EncryptorInterface $encryptor;

    /**
     * Backup constructor.
     * @param Context $context
     * @param Logger $logger
     * @param CurlFactory $curlFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param PowerbiExportRepositoryInterface $powerbiRepository
     * @param EncryptorInterface $encryptor
     */

    public function __construct(
        Context $context,
        Logger $logger,
        CurlFactory $curlFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        PowerbiExportRepositoryInterface $powerbiRepository,
        EncryptorInterface $encryptor
    ) {
        $this->logger = $logger;
        $this->curlFactory = $curlFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->powerbiRepository = $powerbiRepository;
        $this->encryptor = $encryptor;
        parent::__construct($context);
    }

    /**
     * Get PowerBI Config
     *
     * @param string $path
     * @return mixed
     */
    public function getPowerbiConfig(string $path): mixed
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get API Key
     *
     * @return mixed
     */
    public function getApiKey(): mixed
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::MBI_EXPORT_API_KEY));
    }

    /**
     * Get Report Response
     *
     * @param string $reportId
     * @return string|void
     */
    public function getReportResponse(string $reportId)
    {
        if (!empty($reportId)) {
            $curl = $this->curlFactory->create();
            $curl->setOption(CURLOPT_RETURNTRANSFER, true);
            $curl->setOption(CURLOPT_CUSTOMREQUEST, 'POST');
            $curl->setOption(CURLOPT_HTTPHEADER, [
                'X-RJM-API-Key:'.$this->getApiKey(),
                'Content-Type: application/x-www-form-urlencoded'
            ]);

            $apiUrl = $this->getPowerbiConfig(self::MBI_EXPORT_API_URL).$reportId.'/export';
            $curl->post($apiUrl, 'format=csv&includeColumnHeaders=1');
            $response = $curl->getBody();

            if ($curl->getStatus() == '200') {
                return $response;
            } else {
                $this->logger->info(sprintf('Error occurs while export: '.$reportId));
                $this->logger->critical(sprintf('Exception while MBI report export: ' . $response));
            }
        }
    }

    /**
     * Get ifExistRecord
     *
     * @param array $data
     * @return bool|array
     * @throws LocalizedException
     */
    public function ifExistingRecord(array $data): bool|array
    {
        $result = [];
        $filter1 = $this->filterBuilder->setField("report_id");
        $filter1 = $filter1->setValue($data['report_id'])->setConditionType("eq")->create();
        $filterGroup1 = $this->filterGroupBuilder->setFilters([$filter1])->create();
        $searchCriteria = $this->searchCriteriaBuilder->setFilterGroups([$filterGroup1])->create();
        $results = $this->powerbiRepository->getList($searchCriteria)->getItems();
        if (!empty($results)) {
            foreach ($results as $data) {
                if (!empty($data)) {
                    $result = $data->getData();
                }
            }
        }
        return $result;
    }
}
