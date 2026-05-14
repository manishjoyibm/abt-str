<?php

namespace Abbott\Sarp2\Block\Adminhtml\Subscription\Edit;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Plan\Source\BillingFrequency as BillingFrequencySource;
use Aheadworks\Sarp2\Model\Plan\Source\BillingPeriod as BillingPeriodSource;
use Aheadworks\Sarp2\Model\Plan\Source\RepeatPayments as RepeatPaymentsSource;
use Aheadworks\Sarp2\Model\Plan\Source\RepeatPayments\Converter as RepeatPaymentsConverter;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;

class NextOrderAndPlan extends Template
{
    public $profileManagement;
    /**
     * @var RepeatPaymentsConverter
     */
    private $repeatPaymentsConverter;

    /**
     * @var BillingPeriodSource
     */
    private $billingPeriodSource;

    /**
     * @var BillingFrequencySource
     */
    private $billingFrequencySource;

    /**
     * @var RepeatPaymentsSource
     */
    private $repeatPaymentsSource;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var ProfileInterface
     */
    private $profile;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'Abbott_Sarp2::subscription/edit/next_order_and_plan.phtml';

    protected $productRepository;
    /**
     * @param Context $context
     * @param RepeatPaymentsConverter $repeatPaymentsConverter
     * @param BillingPeriodSource $billingPeriodSource
     * @param BillingFrequencySource $billingFrequencySource
     * @param RepeatPaymentsSource $repeatPaymentsSource
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Context $context,
        RepeatPaymentsConverter $repeatPaymentsConverter,
        BillingPeriodSource $billingPeriodSource,
        BillingFrequencySource $billingFrequencySource,
        RepeatPaymentsSource $repeatPaymentsSource,
        PriceCurrencyInterface $priceCurrency,
        ProductRepositoryInterface $productRepository,
        ProfileManagementInterface $profileManagement,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->repeatPaymentsConverter = $repeatPaymentsConverter;
        $this->billingPeriodSource = $billingPeriodSource;
        $this->billingFrequencySource = $billingFrequencySource;
        $this->repeatPaymentsSource = $repeatPaymentsSource;
        $this->priceCurrency = $priceCurrency;
        $this->productRepository = $productRepository;
        $this->profileManagement = $profileManagement;
    }

    /**
     * Get profile entity
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set profile entity
     *
     * @param ProfileInterface $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * Get next order info block html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getNextOrderInfoHtml()
    {
        /** @var NextOrderInfo $nextOrderInfoBlock */
        $nextOrderInfoBlock = $this->getLayout()
            ->createBlock(NextOrderInfo::class, 'aw_sarp2.subscription.next_order_and_plan.next_order_info');
        return $nextOrderInfoBlock
            ->setProfile($this->getProfile())
            ->toHtml();
    }

    /**
     * Format repeat payments value
     *
     * @param ProfileInterface $profile
     * @return \Magento\Framework\Phrase
     */
    public function formatRepeatValue($profile)
    {
        $repeatPaymentsOptions = $this->repeatPaymentsSource->getOptions();
        $billingFrequencyOptions = $this->billingFrequencySource->getOptions();
        $billingPeriodOptions = $this->billingPeriodSource->getOptions();

        $planDefinition = $profile->getProfileDefinition();
        $billingFrequency = $planDefinition->getBillingFrequency();
        $billingPeriod = $planDefinition->getBillingPeriod();
        $repeatPayments = $this->repeatPaymentsConverter->toRepeatPayments($billingPeriod, $billingFrequency);
        if ($repeatPayments) {
            return $repeatPaymentsOptions[$repeatPayments];
        }

        return __(
            'Every %1 %2',
            $billingFrequencyOptions[$billingFrequency],
            $billingPeriodOptions[$billingPeriod]
        );
    }

    /**
     * Get subscription plan edit url
     *
     * @param int $planId
     * @return string
     */
    public function getPlanEditUrl($planId)
    {
        return $this->_urlBuilder->getUrl('aw_sarp2/plan/edit', ['plan_id' => $planId]);
    }

    /**
     * Get admin date
     *
     * @param string $date
     * @return \DateTime
     */
    public function getAdminDate($date)
    {
        return $this->_localeDate->date(new \DateTime($date));
    }

    /**
     * Format profile amount
     *
     * @param float $amount
     * @param string $currencyCode
     * @return float
     */
    public function formatProfileAmount($amount, $currencyCode)
    {
        return $this->priceCurrency->format($amount, true, 2, null, $currencyCode);
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->getProfile()) {
            return '';
        }
        return parent::_toHtml();
    }

    public function getProfileItem()
    {
        $profile = $this->getProfile();
        if ($profile) {
            foreach ($profile->getItems() as $item) {
                return $this->productRepository->get($item->getSku());
            }
        }
        return null;
    }

    public function getNextPaymentDate()
    {
        $profile = $this->getProfile();
        $nextPaymentInfo = $this->profileManagement->getNextPaymentInfo($profile->getProfileId());
        $nextPaymentDate = $nextPaymentInfo->getPaymentDate();
        /*$newDate = date("m/d/Y", strtotime($nextPaymentDate));
        return $newDate;*/
        if ($nextPaymentDate) {
            $nextPaymentDateFormatted = $this->formatDate(
                $this->_localeDate->date(new \DateTime($nextPaymentDate)),
                \IntlDateFormatter::MEDIUM
            );
            return $nextPaymentDateFormatted;
        }
        return '';
    }
}
