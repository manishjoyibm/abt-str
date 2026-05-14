<?php

namespace Abbott\Salesrule\Helper;

use Abbott\Salesrule\Model\SalesRuleAddressCodeUsageFactory;
use Abbott\Salesrule\Model\SalesRuleAddressFactory;
use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\Rule;
use Magento\Setup\Exception;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{

    /**
     * @var CouponFactory
     */
    protected $couponFactory;

    /**
     * @var SalesRuleAddressCodeUsageFactory
     */
    protected $salesRuleAddressCodeUsage;

    /**
     * @var SalesRuleAddressFactory
     */
    protected $salesRuleAddress;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Rule
     */
    protected $ruleRepository;

    public const SALES_RULE_ADDRESS = 'sales_rule_address';
    public const SALES_RULE_ADDRESS_CODE_USAGE = 'sales_rule_address_code_usage';

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param CouponFactory $couponFactory
     * @param SalesRuleAddressCodeUsageFactory $salesRuleAddressCodeUsage
     * @param SalesRuleAddressFactory $salesruleaddress
     * @param Rule $ruleRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        CouponFactory $couponFactory,
        SalesRuleAddressCodeUsageFactory $salesRuleAddressCodeUsage,
        SalesRuleAddressFactory $salesruleaddress,
        Rule $ruleRepository,
        LoggerInterface $logger
    ) {
        $this->couponFactory = $couponFactory;
        $this->salesRuleAddressCodeUsage = $salesRuleAddressCodeUsage;
        $this->salesRuleAddress = $salesruleaddress;
        $this->ruleRepository  = $ruleRepository;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * CheckAddressUsedForPromo
     *
     * @param $address
     * @return bool
     */
    public function checkAddressUsedForPromo($address)
    {
        $flag = true;
        $couponCode = $address->getQuote()->getCouponCode();
        $coupon = $this->couponFactory->create();
        $coupon->load($couponCode, 'code');
        $compareAddress = $this->getComparisonAddress($address);
        $addressusageCollection = $this->salesRuleAddressCodeUsage->create()->getCollection()
                             ->addFieldToFilter('rule_id', $coupon->getRuleId());
        $addressusageCollection->getSelect()
                               ->join(
                                   ['sales_rule_address'=>$addressusageCollection->getTable('sales_rule_address')],
                                   'main_table.address_id = sales_rule_address.entity_id',
                                   ['address'=>'sales_rule_address.shipping_address']
                               )
                               ->where('sales_rule_address.shipping_address='."'".$compareAddress."'");
        if ($addressusageCollection->getSize() > 0) {
            $flag = false;
        }
        return $flag;
    }

    /**
     * GetComparisonAddress
     *
     * @param $address
     * @return string
     */
    public function getComparisonAddress($address)
    {
        ($address->getStreetFull() == null) ? ($street = "") : ($street = $address->getStreetFull());
        ($address->getCity() == null) ? ($city = "") : ($city = $address->getCity());
        ($address->getRegion() == null) ? ($region = "") : ($region = $address->getRegion());
        ($address->getPostcode() == null) ? ($postcode = "") : ($postcode = $address->getPostcode());
        return  trim(strtolower($street)).','.trim(strtolower($city)).','.trim(strtolower($region)).','.
            trim(strtolower($postcode));
    }

    /**
     * StoreCouponAddress
     *
     * @param $couponId
     * @param $quote
     * @param $connection
     * @return void
     */
    public function storeCouponAddress($couponId, $quote, $connection)
    {
        try {
            if ($this->isAddressValidationEnable($quote)) {
                $connection->insert(
                    self::SALES_RULE_ADDRESS,
                    [
                        'shipping_address' => $this->getComparisonAddress($quote->getShippingAddress())
                    ]
                );
                $addressLastId = $connection->lastInsertId();
                $coupon = $this->couponFactory->create();
                $coupon->load($couponId, 'coupon_id');
                $connection->insert(
                    self::SALES_RULE_ADDRESS_CODE_USAGE,
                    [
                        'address_id' => $addressLastId,
                        'coupon_id' => $couponId,
                        'rule_id' => $coupon->getRuleId()
                    ]
                );
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * IsAddressValidationEnable
     *
     * @param $quote
     * @return bool
     */
    public function isAddressValidationEnable($quote)
    {
        try {
            $addressValidationFlag = false;
            $coupon = $this->couponFactory->create();
            $coupon->load($quote->getCouponCode(), 'code');
            $rule = $this->ruleRepository->load($coupon->getRuleId());
            if ($rule->getAddressValidation()) {
                $addressValidationFlag = true;
            }
            return $addressValidationFlag;
        } catch (Exception $e) {
            $this->logger->critical(
                'Something went wrong while coupons usage process. ' . $e->getMessage()
            );
        }
    }
}
