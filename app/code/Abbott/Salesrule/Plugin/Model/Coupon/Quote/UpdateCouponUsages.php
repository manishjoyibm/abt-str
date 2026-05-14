<?php


namespace Abbott\Salesrule\Plugin\Model\Coupon\Quote;

use Magento\Quote\Api\Data\CartInterface;
use Magento\SalesRule\Model\Coupon\Usage\Processor as CouponUsageProcessor;
use Magento\SalesRule\Model\Coupon\Usage\UpdateInfo;
use Magento\SalesRule\Model\Coupon\Usage\UpdateInfoFactory;
use Magento\SalesRule\Model\Service\CouponUsagePublisher;
use Psr\Log\LoggerInterface;

class UpdateCouponUsages
{
    /**
     * @var LoggerInterface
     */
    public LoggerInterface $logger;

    /**
     * @var CouponUsagePublisher
     */
    private CouponUsagePublisher $couponUsagePublisher;

    /**
     * @var UpdateInfoFactory
     */
    private UpdateInfoFactory $updateInfoFactory;

    /**
     * @var CouponUsageProcessor
     */
    private CouponUsageProcessor $processor;

    /**
     * Construct function
     *
     * @param CouponUsagePublisher $couponUsagePublisher
     * @param UpdateInfoFactory $updateInfoFactory
     * @param CouponUsageProcessor $processor
     * @param LoggerInterface $logger
     */
    public function __construct(
        CouponUsagePublisher $couponUsagePublisher,
        UpdateInfoFactory $updateInfoFactory,
        CouponUsageProcessor $processor,
        LoggerInterface $logger
    ) {
        $this->couponUsagePublisher = $couponUsagePublisher;
        $this->updateInfoFactory = $updateInfoFactory;
        $this->processor = $processor;
        $this->logger = $logger;
    }

    /**
     * AroundExecute function
     *
     * @param \Magento\SalesRule\Model\Coupon\Quote\UpdateCouponUsages $subject
     * @param callable $proceed
     * @param CartInterface $quote
     * @param bool $increment
     * @return void
     */
    public function aroundExecute(
        \Magento\SalesRule\Model\Coupon\Quote\UpdateCouponUsages $subject,
        callable $proceed,
        CartInterface $quote,
        bool $increment
    ) {
        try {
            if (!$quote->getAppliedRuleIds()) {
                return;
            }
            /** @var UpdateInfo $updateInfo */
            $updateInfo = $this->updateInfoFactory->create();
            $updateInfo->setAppliedRuleIds(explode(',', $quote->getAppliedRuleIds()));
            $updateInfo->setCouponCode((string)$quote->getCouponCode());
            $updateInfo->setCustomerId((int)$quote->getCustomerId());
            $updateInfo->setIsIncrement($increment);
            $updateInfo->setQuoteId($quote->getId());

            $this->processor->updateCouponUsages($updateInfo);
            $this->processor->updateCustomerRulesUsages($updateInfo);
            $this->couponUsagePublisher->publish($updateInfo);
        } catch (\Exception $e) {
            $this->logger->critical(
                'Something went wrong while coupons usage process. ' . $e->getMessage()
            );
        }
    }
}
