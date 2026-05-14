<?php

namespace Abbott\Sarp2\Cron;

use Abbott\Sarp2\Helper\Data as ConfigHelper;
use Abbott\Sarp2\Model\Reminder\AnnualReminder;
use Magento\Store\Model\StoreManagerInterface;
use Abbott\Sarp2\Logger\Logger;

class AnnualReminderCron
{
    private ConfigHelper $config;
    private $annualReminder;
    private StoreManagerInterface $storeManager;
    private Logger $logger;
    
    public function __construct(
        AnnualReminder $annualReminder,
        StoreManagerInterface $storeManager,
        ConfigHelper $config,
        Logger $logger
    ) {
        $this->annualReminder = $annualReminder;
        $this->storeManager = $storeManager;
        $this->config       = $config;
        $this->logger       = $logger;
    }

    /**
     * Perform processing of placed sample payments
     *
     * @return void
     */
    public function execute()
    {
        $isEnabled = $this->config->isEnabled();
        if(!$isEnabled)
            {
                return;
            }
        
        $enabledStores = $this->config->getSelectedStores();
        foreach ($enabledStores as $storeId) {
            try {
                $this->annualReminder->processStore($storeId);
            } catch (\Exception $e) {
                $this->logger->error(
                    sprintf('[Abbott_Sarp2] AnnualReminderCron failed for storeId=%d: %s', $storeId, $e->getMessage()),
                    ['exception' => $e]
                );
            }
        }
    }
}