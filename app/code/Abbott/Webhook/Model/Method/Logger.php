<?php

namespace Abbott\Webhook\Model\Method;

use Psr\Log\LoggerInterface;
use Abbott\Webhook\Helper\CurlHelper;

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
     * @var CurlHelper
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param CurlHelper $helper
     */
    public function __construct(
        LoggerInterface $logger,
        CurlHelper $helper
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
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
        if ($this->helper->enableDebug()) {
            if (getType($data) == 'string') {
                $result[] =  $data;
                $this->logger->debug($message, $result);
            } else {
                $this->logger->debug($message, $data);
            }
        }
    }
}
