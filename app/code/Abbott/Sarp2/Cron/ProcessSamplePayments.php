<?php

namespace Abbott\Sarp2\Cron;

use Aheadworks\Sarp2\Model\Payment\SamplerManagement;

class ProcessSamplePayments extends \Aheadworks\Sarp2\Cron\ProcessSamplePayments
{
    public $scopeConfig;
    /**
     * @var SamplerManagement
     */
    private $samplerManagement;

    /**
     * @param SamplerManagement $samplerManagement
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        SamplerManagement $samplerManagement,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->samplerManagement = $samplerManagement;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Perform processing of placed sample payments
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->scopeConfig->getValue(
            'aw_sarp2/aw_crons3/psp_enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {
            return;
        }
        $this->samplerManagement->revertPayments();
    }
}
