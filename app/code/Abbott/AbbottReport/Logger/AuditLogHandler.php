<?php

declare(strict_types=1);

namespace Abbott\AbbottReport\Logger;

use Monolog\Logger;
use Magento\Framework\Filesystem\DriverInterface;

class AuditLogHandler extends \Magento\Framework\Logger\Handler\Base
{
    public const LOG_DIRECTORY_PATH = 'var/log/';
    public function __construct(
        DriverInterface $filesystem,
                        $filePath = null,
    ) {
        $fileName = \sprintf(self::LOG_DIRECTORY_PATH . '%s.log', 'mboActionLog_'.\date('Y-m-d'));
        parent::__construct($filesystem, $filePath, $fileName);
    }
}
