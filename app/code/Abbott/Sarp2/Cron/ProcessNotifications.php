<?php
namespace Abbott\Sarp2\Cron;

use Aheadworks\Sarp2\Engine\Notification\NotifierInterface;

class ProcessNotifications extends \Aheadworks\Sarp2\Cron\ProcessNotifications
{
    public $scopeConfig;
    /**
     * @var NotifierInterface
     */
    private $notifier;

    /**
     * @param NotifierInterface $notifier
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(NotifierInterface $notifier,\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
        $this->notifier = $notifier;
    }

    /**
     * Perform processing of notifications
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->scopeConfig->getValue(
            'aw_sarp2/aw_crons2/pn_enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {
            return;
        }
        $this->notifier->processNotificationsForToday();
    }
}
