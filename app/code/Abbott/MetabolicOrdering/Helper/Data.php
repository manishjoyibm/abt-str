<?php

namespace Abbott\MetabolicOrdering\Helper;

use Abbott\MetabolicOrdering\Api\MetabolicOrderingRepositoryInterface;
use Abbott\Customerhistory\Model\CustomerhistoryFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Area;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime as DateTimeService;

/**
 * Helper For Metabolic Records
 */
class Data extends AbstractHelper
{
    public const XML_PATH_MODULE_ENABLE = 'metabolic_ordering/metabolic/enable';
    public const XML_PATH_CRON_ENABLE = 'metabolic_ordering/metabolic/enable_cron';
    public const XML_PATH_DAYS = 'metabolic_ordering/metabolic/default_days';
    public const ATTR_LABEL = 'Level1';
    public const ATTR_CODE = 'pre_approval';
    public const XML_BASE = 'metabolic_ordering/';

    /**
     * @var storeManager
     */
    protected $storeManager;
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var InlineTranslation
     */
    protected $inlineTranslation;
    /**
     * @var MetabolicRepositoryInterface
     */
    private $metabolicRepository;
    protected $authSession;
    protected $productRepository;
    protected $customerhistoryFactory;
    protected $filterGroupBuilder;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private FilterBuilder $filterBuilder;
    private $productFactory;

        /**
     * Store-aware timezone provider used for store-local date calculations.
     *
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * Magento DateTime service, used primarily for UTC/GMT timestamps.
     *
     * @var DateTimeService
     */
    protected $dateTime;

    /**
     * Constructor
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param MetabolicOrderingRepositoryInterface $metabolicRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param ProductRepositoryInterface $productRepository
     * @param CustomerhistoryFactory $customerhistoryFactory
     * @param FilterGroupBuilder $filterGroupBuilder
     * * @param TimezoneInterface            $timezone
     * @param DateTimeService              $dateTime
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        MetabolicOrderingRepositoryInterface $metabolicRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        ProductRepositoryInterface $productRepository,
        CustomerhistoryFactory $customerhistoryFactory,
        FilterGroupBuilder $filterGroupBuilder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        TimezoneInterface $timezone,
        DateTimeService $dateTime
    ) {
        $this->productRepository = $productRepository;
        $this->customerhistoryFactory = $customerhistoryFactory;
        $this->storeManager = $storeManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->productFactory = $productFactory;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->metabolicRepository = $metabolicRepository;
        $this->authSession = $authSession;
        $this->timezone           = $timezone;
        $this->dateTime           = $dateTime;
        parent::__construct($context);
    }

    public function getCurrentUser()
    {
        return $this->authSession->getUser();
    }

     /**
      * Get the store Id
      *
      * @return int
      */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
     /**
      * Get the Module Config
      *
      * @param string $path
      * @return mixed
      */
    public function getModuleConfig($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }
    /**
     * Get Status of Module.
     *
     * @return mixed
     */
    public function getModuleEnable()
    {
        return $this->getModuleConfig(self::XML_PATH_MODULE_ENABLE);
    }

    /**
     * Get Status of CRON.
     *
     * @return mixed
     */
    public function getCronEnable()
    {
        return $this->getModuleConfig(self::XML_PATH_CRON_ENABLE);
    }

    /**
     * Get No of days
     *
     * @return mixed
     */
    public function getDefaultDays()
    {
        return $this->getModuleConfig(self::XML_PATH_DAYS);
    }

    /**
     * Get No of days
     *
     * @param array $data
     * @return bool
     */
    public function ifExistingRecord($data)
    {
        $result = [];
        $filter1 = $this->filterBuilder->setField("sku")->setValue($data['sku'])->setConditionType("eq")->create();
        $filterGroup1 = $this->filterGroupBuilder->setFilters([$filter1])->create();
        $filter2 = $this->filterBuilder->setField("customer_email")->setValue($data['customer_email'])
            ->setConditionType("eq")->create();
        $filterGroup2 = $this->filterGroupBuilder->setFilters([$filter2])->create();
        $searchCriteria = $this->searchCriteriaBuilder->setFilterGroups([$filterGroup1, $filterGroup2])->create();
        $results = $this->metabolicRepository->getList($searchCriteria)->getItems();
        if (!empty($results)) {
            foreach ($results as $data) {
                if (!empty($data)) {
                    $result = $data->getData();
                }
            }
        }
        return $result;
    }

    /**
     * Load only metabolic products by skus.
     *
     * @return array
     */
    public function filterProducts()
    {
        $optionId = $this->getAttrOptIdByLabel();
        $this->searchCriteriaBuilder->addFilter(
            self::ATTR_CODE,
            $optionId,
            'eq'
        );
        $products = $this->productRepository->getList($this->searchCriteriaBuilder->create())
            ->getItems();
        $sku = [];
        if ($products) {
            foreach ($products as $product) {
                $sku[] = $product->getSku();
            }
            sort($sku);
        }
        return $sku;
    }

    /**
     * Update Comments
     *
     * @param array $data
     * @return void
     */
    public function updateComments($data)
    {
        $customerHistory = $this->customerhistoryFactory->create();
        $customerHistory->setCustomerId($data['customer_id'])->setComments($data['comment'])
            ->setFlag('comments')->setAdminUsername($data['admin_user'])->save();
    }

    /**
     * Get option Id By label
     *
     * @return int
     */
    public function getAttrOptIdByLabel()
    {
        $product = $this->productFactory->create();
        $isAttrExist = $product->getResource()->getAttribute(self::ATTR_CODE);
        $optId = '';
        if ($isAttrExist && $isAttrExist->usesSource()) {
            $optId = $isAttrExist->getSource()->getOptionId(self::ATTR_LABEL);
        }
        return $optId;
    }

    /**
     * Get Approval attribute value
     *
     * @param string $sku
     * @return int
     * @throws NoSuchEntityException
     */
    public function getLevelAttributeId($sku)
    {
        $result = $this->productRepository->get($sku);
        return $result->getPreApproval();
    }

     /**
      * Get Approval attribute label
      *
      * @param string $sku
      * @return string
      * @throws NoSuchEntityException
      */
    public function getLevelAttributeLabel($sku)
    {
        $result = $this->productRepository->get($sku);
        return $result->getAttributeText("pre_approval");
    }

    /**
     * Get Expiry Enabled Config
     *
     * @return bool
     */
    public function expiryEnabled($storeId = null): bool
    {
        return $this->getModuleConfig(self::XML_BASE . 'expiry_notifications/enabled');
    }

    /**
     * Get expiry days
     *
     * @return int
     */
    public function expiryDays($storeId = null): int
    {
        return (int)$this->getModuleConfig(self::XML_BASE . 'expiry_notifications/days_before_expiry');
    }

    /**
     * Email sender
     *
     * @return string
     */
    public function expirySender($storeId = null): string
    {
        return (string)$this->getModuleConfig(self::XML_BASE . 'expiry_notifications/sender');
    }

    /**
     * Get Email Template
     *
     * @return string
     */
    public function expiryTemplate($storeId = null): string
    {
        return (string)$this->getModuleConfig(self::XML_BASE . 'expiry_notifications/template');
    }

    /**
     * Cron for function
     *
     * @return string
     */
    public function expiryCronExpr($storeId = null): ?string
    {
        $expr = trim((string)$this->getModuleConfig(self::XML_BASE . 'expiry_notifications/cron_expr'));
        return $expr ?: null;
    }

    /**
     * Get thresholdEnabled Config
     *
     * @return bool
     */
    public function thresholdEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_BASE . 'threshold_notifications/enabled');
    }

    /**
     * Threshold Qty
     *
     * @return int
     */
    public function thresholdQty($storeId = null): int
    {
        return (int)$this->getModuleConfig(self::XML_BASE . 'threshold_notifications/threshold_qty');
    }

    /**
     * Email sender
     *
     * @return string
     */
    public function thresholdSender($storeId = null): string
    {
        return (string)$this->getModuleConfig(self::XML_BASE . 'threshold_notifications/sender');
    }
    
     /**
     * Get Email Template
     *
     * @return string
     */
    public function thresholdTemplate($storeId = null): string
    {
        return (string)$this->getModuleConfig(self::XML_BASE . 'threshold_notifications/template');
    }

    
    /**
     * Get the current time in UTC (GMT) formatted as 'Y-m-d H:i:s'.
     *
     * Uses Magento's DateTime service to return the current timestamp in GMT.
     *
     * @return string Current UTC time in 'Y-m-d H:i:s' format.
     */
    public function getCurrentTime()
    {
       return  $this->dateTime->gmtDate('Y-m-d H:i:s');
    }

    
    /**
     * Calculate the target date for expiry notifications.
     *
     * Retrieves the configured number of days before expiry from module settings
     * and adds that interval to the current store-local date.
     *
     * @return string Target date in 'Y-m-d' format.
     */
    public function getTargetDate(){
        $daysBefore = (int)$this->getModuleConfig(self::XML_BASE . 'expiry_notifications/days_before_expiry');
        return $this->timezone
                ->date() // store-local DateTime (mutable)
                ->modify(sprintf('+%d days', $daysBefore))
                ->format('Y-m-d');
    }

    
    /**
     * Get today's date in store-local timezone.
     *
     * Uses Magento's TimezoneInterface to return the current date for the store.
     *
     * @return string Today's date in 'Y-m-d' format.
     */
    public function getToday()
    {
        return $this->timezone->date()->format('Y-m-d');
    }
}
