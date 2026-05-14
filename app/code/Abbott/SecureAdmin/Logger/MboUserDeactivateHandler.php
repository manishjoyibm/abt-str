<?php

declare(strict_types=1);

namespace Abbott\SecureAdmin\Logger;

use Monolog\Logger;
use Magento\Framework\Filesystem\DriverInterface;

class MboUserDeactivateHandler extends \Magento\Framework\Logger\Handler\Base
{
    public const LOG_DIRECTORY_PATH = 'var/log/customer_deactivated/';
    public function __construct(
        DriverInterface $filesystem,
        $filePath = null,
    ) {
        $fileName = \sprintf(self::LOG_DIRECTORY_PATH . '%s.log', 'mbo_user_deactivated_cron_'.\date('Y-m-d'));
        parent::__construct($filesystem, $filePath, $fileName);
    }
}