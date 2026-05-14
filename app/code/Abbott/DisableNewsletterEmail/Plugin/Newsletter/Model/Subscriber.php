<?php

namespace Abbott\DisableNewsletterEmail\Plugin\Newsletter\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Subscriber
{
    private const DISABLE_NEWSLETTER_EMAIL = 'newsletter/subscription/disable_newsletter_success';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Before Send Confirmation SuccessEmail
     *
     * @param \Magento\Newsletter\Model\Subscriber $subject
     * @return void
     */
    public function beforeSendConfirmationSuccessEmail(\Magento\Newsletter\Model\Subscriber $subject): void
    {
        if ($this->scopeConfig->getValue(
            self::DISABLE_NEWSLETTER_EMAIL,
            ScopeInterface::SCOPE_STORE
        )) {
            $subject->setImportMode(true);
        }
    }

    /**
     * Before Send Unsubscription Email
     *
     * @param \Magento\Newsletter\Model\Subscriber $subject
     * @return void
     */
    public function beforeSendUnsubscriptionEmail(\Magento\Newsletter\Model\Subscriber $subject): void
    {
        if ($this->scopeConfig->getValue(
            self::DISABLE_NEWSLETTER_EMAIL,
            ScopeInterface::SCOPE_STORE
        )) {
            $subject->setImportMode(true);
        }
    }
}
