<?php

namespace Abbott\Chargeback\Model\System\Config\Backend\SftpSync;

use Exception;
use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Backend model for import/export log cleaning schedule options
 */
class Cron extends \Magento\Framework\App\Config\Value
{
    /**
     * Cron expression configuration path
     */
    public const CRON_STRING_PATH = 'crontab/default/jobs/chargeback_sftp_sync_cron/schedule/cron_expr';

    /**
     * @var ValueFactory
     */
    protected ValueFactory $configValueFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ValueFactory $configValueFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ValueFactory $configValueFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Add cron task
     *
     * @throws Exception
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function afterSave()
    {
        $time = $this->getData('groups/chargeback_cron/fields/time/value');
        $frequency = $this->getData('groups/chargeback_cron/fields/frequency/value');

        $frequencyWeekly = Frequency::CRON_WEEKLY;
        $frequencyMonthly = Frequency::CRON_MONTHLY;

        $cronExprArray = [
            (int)$time[1],                                   // Minute
            (int)$time[0],                                   // Hour
            $frequency == $frequencyMonthly ? '1' : '*',        // Day of the Month
            '*',                                                // Month of the Year
            $frequency == $frequencyWeekly ? '1' : '*',          // Day of the Week
        ];

        $cronExprString = join(' ', $cronExprArray);

        try {
            $this->configValueFactory->create()->load(
                self::CRON_STRING_PATH,
                'path'
            )->setValue(
                $cronExprString
            )->setPath(
                self::CRON_STRING_PATH
            )->save();
        } catch (Exception $e) {
            throw new LocalizedException(
                __('We were unable to save the cron expression.')
            );
        }
        return parent::afterSave();
    }
}
