<?php
namespace Abbott\Webhook\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Abbott\Webhook\Helper\CurlHelper;
use Abbott\Webhook\Model\Method\Logger as WebhookLogger;

class AttributeSaveAfter implements ObserverInterface
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
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $attrCode = $observer->getEvent()->getAttribute()->getAttributeCode();
        $this->webhooklog->debug('Webhook-Attribute-Code', $attrCode);
        $attrCodes = explode(",", $this->helper->getAttributeCodes());
        if ($this->helper->enabled() && in_array($attrCode, $attrCodes)) {
            $code = $attrCode == 'flavors' ? 'flavor' : $attrCode;
            $url = $this->helper->getFlavorSizeUrl()."?id=".$code;
            try {
                $this->webhooklog->debug('Webhook-Attribute-Url', $url);
                $response = $this->helper->postData($url);
                $this->webhooklog->debug('Webhook-Attribute-Url-Response', $response);
            } catch (\Exception $ex) {
                $this->webhooklog->debug('Webhook-Attribute-Exception', $ex->getMessage());
            }
        }
    }
}
