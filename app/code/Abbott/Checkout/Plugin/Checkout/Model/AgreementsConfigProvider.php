<?php

namespace Abbott\Checkout\Plugin\Checkout\Model;

class AgreementsConfigProvider
{
    public $customerHelper;
    /**
     * @var \Abbott\MyAccount\Helper\Data
     */
    public $accountHelper;
    public function __construct(
        \Abbott\CustomerTransistion\Helper\Data $customerHelper,
        \Abbott\MyAccount\Helper\Data $accountHelper
    ) {
        $this->customerHelper = $customerHelper;
        $this->accountHelper = $accountHelper;
    }
    public function afterGetConfig(\Magento\CheckoutAgreements\Model\AgreementsConfigProvider $subject, array $result)
    {
        if ($this->customerHelper->getFailureUrl()) {
            $result['checkoutAgreements']['termslink'] = $this->customerHelper->getFailureUrl()
                .$this->accountHelper->getRedirectConfig('checkout_terms_url');
        }

        return $result;
    }
}
