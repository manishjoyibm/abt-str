<?php

namespace Abbott\CreditCards\Model\Method;

use Psr\Log\LoggerInterface;

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
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
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
        if (getType($data) == 'string') {
            $result[] =  $data;
            $this->logger->debug($message, $result);
        } else {
            $this->logger->debug($message, $data);
        }
    }
}
