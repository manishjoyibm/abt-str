<?php

namespace Abbott\MetabolicOrdering\Cron;

use Abbott\MetabolicOrdering\Api\MetabolicOrderingRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Abbott\MetabolicOrdering\Helper\Data;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class RemoveRecords
{
    /**
     * @var MetabolicOrderingRepositoryInterface
     */
    private $metabolicRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
     /**
      * @var helper
      */
    protected $helper;
    /**
     * @var logger
     */
    private $logger;

    protected $timezoneInterface;

    /**
     * Constructor
     *
     * @param MetabolicOrderingRepositoryInterface $metabolicRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Data $helper
     */
    public function __construct(
        MetabolicOrderingRepositoryInterface $metabolicRepository,
        TimezoneInterface $timezoneInterface,
        \Psr\Log\LoggerInterface $logger,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Data $helper
    ) {
        $this->metabolicRepository = $metabolicRepository;
        $this->logger = $logger;
        $this->timezoneInterface = $timezoneInterface;
        $this->helper = $helper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

     /**
      * Cron to set qty 0 for expired records
      *
      * @return void
      */
    public function execute()
    {
        if ($this->helper->getCronEnable()) {
            try {
                $currentDate = $this->timezoneInterface->date()->format('Y-m-d');
                $this->searchCriteriaBuilder->addFilter('expiry_date', $currentDate, 'eq');
                $results = $this->metabolicRepository->getList($this->searchCriteriaBuilder->create())
                   ->getItems();
                if (!empty($results)) {
                    foreach ($results as $data) {
                        $data->setQty(0);
                        $data->save();
                        $request['comment'] =  'Product approval for sku: '.$data->getSku().' expired on :'
                            .$data->getExpiryDate();
                        $request['admin_user'] = $data->getAdminUser();
                        $request['customer_id'] = $data->getCustomerId();
                        $this->helper->updateComments($request);
                    }
                }
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
