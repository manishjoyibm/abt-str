<?php

namespace Abbott\Hartehanks\Model\Method;

use Psr\Log\LoggerInterface;
use Abbott\Hartehanks\Helper\Transport;

/**
 * Class Logger for HH related information (request, response, etc.) which is used for debug.
 *
 * @api
 * @since 100.0.2
 */
class Logger
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Transport
     */
    protected $transportHelper;

    /**
     * @param LoggerInterface $logger
     * @param Transport $transportHelper
     */
    public function __construct(
        LoggerInterface $logger,
        Transport $transportHelper
    ) {
        $this->logger = $logger;
        $this->transportHelper = $transportHelper;
    }

    /**
     * Logs HH related information used for debug
     *
     * @param string $message
     * @param array|string $data
     * @return void
     */
    public function debug($message, $data)
    {
        if ($this->transportHelper->enableDebug()) {
            if (getType($data) == 'string') {
                $result[] =  $data;
                $this->logger->debug($message, $result);
            } else {
                $this->logger->debug($message, $data);
            }
        }
    }

    /**
     * Logs HH related information used for debug
     *
     * @param string $message
     * @param array|string $data
     * @return void
     */
    public function advanceDebug($message, $data)
    {
        if ($this->transportHelper->isAdvanceDebugEnabled()) {
            if (getType($data) == 'string') {
                $result[] =  $data;
                $this->logger->debug($message, $result);
            } else {
                $this->logger->debug($message, $data);
            }
        }
    }
}
