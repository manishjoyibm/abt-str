<?php


namespace Abbott\Salesrule\Plugin\Model\ResourceModel\Coupon;

use Abbott\Salesrule\Helper\Data;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Model\QuoteFactory;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\Rule;
use Magento\Setup\Exception;
use Psr\Log\LoggerInterface;

class Usage
{
    /**
     * @var CouponFactory
     */
    public $couponFactory;
    /**
     * @var Rule
     */
    public $ruleRepository;
    public $checkoutSession;
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule\CustomerFactory
     */
    protected $customerFactory;

    protected $helper;

    public const TABLE_NAME = 'salesrule_coupon_usage';

    /**
     * Usage constructor.
     *
     * @param ResourceConnection $resource
     * @param DateTime $date
     * @param QuoteFactory $quoteFactory
     * @param LoggerInterface $logger
     * @param CouponFactory $couponFactory
     * @param Rule $ruleRepository
     * @param CheckoutSession $checkoutSession
     * @param Data $helper
     */
    public function __construct(
        ResourceConnection $resource,
        DateTime $date,
        QuoteFactory $quoteFactory,
        LoggerInterface $logger,
        CouponFactory $couponFactory,
        Rule $ruleRepository,
        CheckoutSession $checkoutSession,
        Data $helper
    ) {
        $this->resource = $resource;
        $this->date = $date;
        $this->quoteFactory = $quoteFactory;
        $this->logger = $logger;
        $this->couponFactory = $couponFactory;
        $this->ruleRepository  = $ruleRepository;
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
    }

    /**
     * GetConnection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection()
    {
        return $this->resource->getConnection();
    }

    /**
     * AroundUpdateCustomerCouponTimesUsed
     *
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon\Usage $subject
     * @param callable $proceed
     * @param $customerId
     * @param $couponId
     * @param $quoteId
     * @param $increment
     * @return void
     */
    public function aroundUpdateCustomerCouponTimesUsed(
        \Magento\SalesRule\Model\ResourceModel\Coupon\Usage $subject,
        callable $proceed,
        $customerId,
        $couponId,
        $quoteId,
        $increment = true
    ): void {
        try {
            $connection = $this->getConnection();
            $select = $connection->select();
            $select->from(
                self::TABLE_NAME,
                ['times_used','quote_id','created_at']
            )->where(
                'coupon_id = :coupon_id'
            )->where(
                'customer_id = :customer_id'
            );
            $currentQuoteId = $this->getQuoteId();
            $customerCouponUsage = $connection->fetchRow($select, [':coupon_id' => $couponId, ':customer_id'
            => $customerId]);
            $quote = $this->quoteFactory->create()->load($currentQuoteId);
            if (is_array($customerCouponUsage) && $customerCouponUsage !== false) {
                if (empty($customerCouponUsage['quote_id']) || is_null($customerCouponUsage['quote_id'])) {
                    $quoteIdCurrent = $currentQuoteId;
                } else {
                    $quoteIdCurrent = $customerCouponUsage['quote_id'].','.$currentQuoteId;
                }
                if (empty($customerCouponUsage['created_at']) || is_null($customerCouponUsage['created_at'])) {
                    $createdAt = $this->date->gmtDate();
                } else {
                    $createdAt = $customerCouponUsage['created_at'].','.$this->date->gmtDate();
                }

                $this->getConnection()->update(
                    self::TABLE_NAME,
                    [
                        'times_used' => $customerCouponUsage['times_used'] + ($increment ? 1 : -1),
                        'quote_id' => $quoteIdCurrent,
                        'created_at' => $createdAt
                    ],
                    ['coupon_id = ?' => $couponId, 'customer_id = ?' => $customerId]
                );
            } elseif ($increment) {
                $this->getConnection()->insert(
                    self::TABLE_NAME,
                    [
                        'coupon_id' => $couponId,
                        'customer_id' => $customerId,
                        'times_used' => 1,
                        'quote_id' => $currentQuoteId,
                        'created_at' => $this->date->gmtDate()
                    ]
                );
            }
            /**
             *  Store Address for Promo code used
             *  Jira ANAPOLLO-2903
             */
            $this->helper->storeCouponAddress($couponId, $quote, $this->getConnection());

        } catch (\Exception $e) {
            $this->logger->critical(
                'Something went wrong while coupons usage process. ' . $e->getMessage()
            );
        }
    }

    /**
     * Checkout quote id
     *
     * @return int|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuoteId()
    {
        return $this->checkoutSession->getQuote()->getId();
    }
}
