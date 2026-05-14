<?php


namespace Abbott\NewRelicReports\Model;


/**
 * Class NewRelicWrapper
 * This is extension of magento native module for new relic adding some wrapper functions for additional new relic
 * capabilities
 * @package Abbott\NewRelicReports\Model
 */
class NewRelicWrapper extends \Magento\NewRelicReporting\Model\NewRelicWrapper
{
    /**
     * Wrapper for 'newrelic_record_custom_event'
     *
     * @param string $eventName
     * @param array $data
     * @return void
     */
    public function recordCustomEvent(string $eventName, array $data)
    {
        if ($this->isExtensionInstalled()) {
            newrelic_record_custom_event($eventName, $data);
        }
    }
}
