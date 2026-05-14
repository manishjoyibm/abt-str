<?php

namespace Abbott\Sarp2\Block\Adminhtml\Subscription\Info;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class NextOrderInfo extends Template
{
    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

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
    protected $_template = 'Abbott_Sarp2::subscription/edit/next_order_info.phtml';

    /**
     * @param Context $context
     * @param ProfileManagementInterface $profileManagement
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProfileManagementInterface $profileManagement,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->profileManagement = $profileManagement;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Get profile entity
     *
     * @return ProfileInterface
     */
    private function getProfile()
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
     * Get next order date info
     *
     * @return string
     */
    public function getNextOrderDateInfo()
    {
        $profile = $this->getProfile();
        if ($this->isNextOrderInfoAvailable()) {
            $nextPaymentInfo = $this->profileManagement->getNextPaymentInfo($profile->getProfileId());
            $nextPaymentDate = $nextPaymentInfo->getPaymentDate();
            if ($nextPaymentDate) {
                $nextPaymentDateFormatted = $this->formatDate(
                    $this->_localeDate->date(new \DateTime($nextPaymentDate)),
                    \IntlDateFormatter::MEDIUM
                );
                $paymentStatus = $nextPaymentInfo->getPaymentStatus();
                return $paymentStatus == ScheduledPaymentInfoInterface::PAYMENT_STATUS_REATTEMPT
                    ? __('Scheduled payment was failed. The next charge attempt: %1', $nextPaymentDateFormatted)
                    : __('Date: %1', $nextPaymentDateFormatted);
            }
            return '';
        } else {
            return $profile->getStatus() == Status::ACTIVE
                ? __('All payments are done')
                : ($profile->getStatus() == Status::EXPIRED ? __('Finished') : __('Cancelled'));
        }
    }

    /**
     * Get membership is paid until date
     *
     * @return string
     */
    public function getMembershipPaidDateInfo()
    {
        $profile = $this->getProfile();
        if ($this->isSubscriptionAvailable()) {
            $nextPaymentInfo = $this->profileManagement->getNextPaymentInfo($profile->getProfileId());
            $nextPaymentDate = $nextPaymentInfo->getPaymentDate();
            if ($nextPaymentDate) {
                $nextPaymentDateFormatted = $this->formatDate(
                    $this->_localeDate->date(new \DateTime($nextPaymentDate)),
                    \IntlDateFormatter::MEDIUM
                );
                return $nextPaymentDateFormatted;
            }
        }

        return $profile->getStatus() == Status::EXPIRED ? __('Finished') : __('Cancelled');
    }

    /**
     * Get next order amount html
     *
     * @return string
     */
    public function getNextOrderAmountHtml()
    {
        $profile = $this->getProfile();
        if ($this->isNextOrderInfoAvailable()) {
            $nextPaymentInfo = $this->profileManagement->getNextPaymentInfo($profile->getProfileId());
            $formattedAmount = $this->priceCurrency->format(
                $nextPaymentInfo->getBaseAmount(),
                true,
                2,
                null,
                $profile->getBaseCurrencyCode()
            );
            return __('Order Total: %1', $formattedAmount);
        }
        return '';
    }

    /**
     * Check if next order info available
     *
     * @return bool
     */
    public function isNextOrderInfoAvailable()
    {
        $profile = $this->getProfile();
        $result = $this->isSubscriptionAvailable();
        if ($result) {
            $nextPaymentInfo = $this->profileManagement->getNextPaymentInfo($profile->getProfileId());
            $result = !($nextPaymentInfo->getPaymentStatus()
                == ScheduledPaymentInfoInterface::PAYMENT_STATUS_LAST_PERIOD_HOLDER);
        }

        return $result;
    }

    /**
     * Check if subscription available
     *
     * @return bool
     */
    public function isSubscriptionAvailable()
    {
        return !in_array(
            $this->getProfile()->getStatus(),
            [Status::CANCELLED, Status::EXPIRED]
        );
    }

    /**
     * Check is membership model enabled
     *
     * @return bool
     */
    public function isMembershipModelEnabled()
    {
        return $this->getProfile()->getProfileDefinition()->getIsMembershipModelEnabled();
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
}
