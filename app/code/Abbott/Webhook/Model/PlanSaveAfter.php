<?php
namespace Abbott\Webhook\Model;

use Abbott\Webhook\Helper\CurlHelper;
use Abbott\Webhook\Model\Method\Logger as WebhookLogger;

class PlanSaveAfter
{
    public $helper;
    public $webhooklog;

    /**
     * Constructor
     *
     * @param CurlHelper $helper
     * @param WebhookLogger $webhooklog
     */
    public function __construct(
        CurlHelper $helper,
        WebhookLogger $webhooklog
    ) {
        $this->helper = $helper;
        $this->webhooklog = $webhooklog;
    }

    /**
     * Execute
     *
     * @return void
     */
    public function execute()
    {
        $this->webhooklog->debug('Webhook-Subscription-Plan', "Entered");
        if ($this->helper->enabled()) {
            $url = $this->helper->getFlavorSizeUrl().'?id=subscription';
            try {
                $this->webhooklog->debug('Webhook-Subscription-Plan', $url);
                $response = $this->helper->postData($url);
                $this->webhooklog->debug('Webhook-Subscription-Plan-Response', $response);
            } catch (\Exception $ex) {
                $this->webhooklog->debug('Webhook-Subscription-Plan-Exception', $ex->getMessage());
            }
        }
    }
}
