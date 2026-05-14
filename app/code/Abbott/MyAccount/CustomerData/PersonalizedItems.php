<?php

declare(strict_types=1);

namespace Abbott\MyAccount\CustomerData;

use Abbott\AwsLambda\Logger\Log;
use Aheadworks\Sarp2\Model\PlanFactory;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Abbott\ProgressiveDiscount\Helper\Data;

/**
 * Returns information for "Personaized Product".
 */
class PersonalizedItems
{
    public $orderCollFactory;
    public $_orderConfig;
    public $_customerSession;
    public $log;
    public $planFactory;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Construct function
     *
     * @param CollectionFactory $orderCollFactory
     * @param Config $orderConfig
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     * @param Log $log
     * @param Data $helper
     * @param PlanFactory $planFactory
     */
    public function __construct(
        CollectionFactory $orderCollFactory,
        Config $orderConfig,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        Log $log,
        Data $helper,
        PlanFactory $planFactory
    ) {
        $this->storeManager = $storeManager;
        $this->orderCollFactory = $orderCollFactory;
        $this->_orderConfig = $orderConfig;
        $this->_customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->log = $log;
        $this->helper = $helper;
        $this->planFactory = $planFactory;
    }

    /**
     * Init last placed customer order for display on front
     *
     * @param $customerId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getOrderItems($customerId = null)
    {
        $this->log->writeLog('Inside personalised Graphql');
        $items[] = ['status' => false];
        if (!$customerId) {
            $customerId = $this->_customerSession->getCustomerId();
        }
        $this->log->writeLog('customer id:'.$customerId);
        //check for customer don't have progressive subscription active
        if (!$this->helper->isSubscriptionActive($customerId)) {
            $this->log->writeLog('Inside Iif no subscription active');
            $orders = $this->orderCollFactory->create()
                ->addAttributeToFilter('customer_id', $customerId)
                ->addAttributeToFilter('store_id', $this->storeManager->getStore()->getId())
                ->addAttributeToFilter('status', ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()])
                ->addAttributeToSort('created_at', 'desc');
            $progressivePlanArray = $this->getProgressivePlans();
            $this->log->writeLog('get progressive plan:'.print_r($progressivePlanArray, true));
            foreach ($orders as $order):
                foreach ($order->getAllVisibleItems() as $orderItem) {
                    $options = $orderItem->getProductOptions();
                    $this->log->writeLog('get product option:'.print_r($options, true));
                    if (!array_key_exists('aw_sarp2_subscription_plan', $options)) {
                        $product = $this->productRepository->getById(
                            $orderItem->getProductId(),
                            false,
                            $this->storeManager->getStore()->getId()
                        );
                        if (isset($product)) {
                            //check wether product is mapped with progressive plan
                            $productPlans = $product->getAttributeText('plans');
                            $this->log->writeLog('get product plan:'.print_r($productPlans, true));
                            $isProgressivePlanMapped = $this->checkForProgressivePlan(
                                $productPlans,
                                $progressivePlanArray
                            );
                            if ($isProgressivePlanMapped) {
                                 $item[] = [
                                    'status' => true,
                                    'aem_url' => $product->getAemUrl(),
                                    'dam_images' => $product->getDamImages()
                                 ];
                                 $this->log->writeLog('personalization response in'.print_r($item, true));
                                 return $item;
                            }
                        }
                    }
                }
            endforeach;
        }
        $this->log->writeLog('personalization response'.print_r($items, true));
        return $items;
    }

    /**
     * CheckForProgressivePlan function
     *
     * @param $plans
     * @param $progressivePlanArray
     * @return bool
     */
    private function checkForProgressivePlan($plans, $progressivePlanArray)
    {
        if (!empty($plans) && !empty($progressivePlanArray)) {
            $result = array_intersect($plans, $progressivePlanArray);
            $this->log->writeLog('get plan result:'.print_r($result, true));
            if (!empty($result)) {
                return true;
            }
        }
        return false;
    }

    /**
     * GetProgressivePlans function
     *
     * @return array
     */
    private function getProgressivePlans()
    {
        $plans = [];
        $planCollection = $this->planFactory->create()->getCollection()
                                       ->addFieldToSelect(['plan_id'])
                                       ->addFieldToFilter('status', ['eq' => 1])
                                       ->addFieldToFilter('is_progressive', ['eq' => 1])
                                       ->getData();
        if (!empty($planCollection)) {
            $plans = array_column($planCollection, 'plan_id');
        }
        return $plans;
    }
}
