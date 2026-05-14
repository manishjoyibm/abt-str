<?php

namespace Abbott\MyAccount\Block\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;

class Title extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Construct function
     *
     * @param Context $context
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
    }

    /**
     * GetCustomerSession function
     *
     * @return Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }
}
