<?php
namespace Abbott\Chargeback\Model;

use Abbott\Chargeback\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ChargebackDeleteDbLog extends \Magento\Framework\Model\AbstractModel
{
    private const CRON_STRING_PATH = 'chargeback_settings/chargeback_cron/dl_enabled';

    /**
     * @var Data
     */
    protected Data $chargebackData;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * Targetbase FileCreation constructor.
     *
     * @param Data $chargebackData
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Data $chargebackData,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->chargebackData = $chargebackData;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * This cron is used to create both customer and order files
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->scopeConfig->getValue(
            self::CRON_STRING_PATH
        )) {
            return;
        }
        $this->chargebackData->deleteDbLog();
    }
}
