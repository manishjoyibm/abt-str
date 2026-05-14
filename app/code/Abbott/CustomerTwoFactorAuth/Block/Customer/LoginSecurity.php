<?php
namespace Abbott\CustomerTwoFactorAuth\Block\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Abbott\CustomerTwoFactorAuth\Helper\Data;
use Magento\Framework\Stdlib\DateTime\DateTime;

class LoginSecurity extends Template
{
    /**
     * @var \Abbott\CustomerTwoFactorAuth\Helper\Data
     */
    protected $helper;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @param Context $context
     * @param Data $helper
     * @param DateTime $dateTime
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        DateTime $dateTime,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->dateTime = $dateTime;
        parent::__construct($context, $data);
    }
    /**
     * Checking whether the Customer Login Security attribute enabled or not.
     *
     * @return bool
     */
    public function isCustomerTwoFAEnabled()
    {
        return $this->helper->isCustomerSecurityEnabled();
    }

    /**
     * Get end time
     *
     * @return mixed
     */
    public function getEndTime()
    {
         return $this->helper->getExpiryLimit();
    }
}
