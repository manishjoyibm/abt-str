<?php
namespace Abbott\Webhook\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Abbott\Webhook\Model\ResourceModel\Webhook\CollectionFactory;
use Abbott\Webhook\Helper\CurlHelper;
use Abbott\Webhook\Model\Method\Logger as WebhookLogger;

class CategorySaveAfter implements ObserverInterface
{
    private const CATALOG_CATEGORY_SAVE = 'catalog_category_save_after';

    /**
     * @var CurlHelper
     */
    protected $helper;

    /**
     * @var WebhookLogger
     */
    protected $webhooklog;

    /**
     * @var CollectionFactory
     */
    protected $webhookFactory;


    /**
     * @param CollectionFactory $webhookFactory
     * @param CurlHelper $helper
     * @param WebhookLogger $webhooklog
     */
    public function __construct(
        CollectionFactory $webhookFactory,
        CurlHelper $helper,
        WebhookLogger $webhooklog
    ) {
        $this->webhookFactory = $webhookFactory;
        $this->helper = $helper;
        $this->webhooklog = $webhooklog;
    }

    /**
     * Execute
     *
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        if ($this->helper->enabled()) {
            $category = $observer->getEvent()->getCategory();
            $catId = $category->getId();
            $this->webhooklog->debug('Webhook-CategoryID', $catId);
            $collection = $this->webhookFactory->create();
            $result = $collection->addFieldToFilter(
                'event_name',
                ['eq' => self::CATALOG_CATEGORY_SAVE]
            )->getFirstItem();
            $url = $result->getPath();
            $url = $url . "?id=" . $catId;
            try {
                $this->webhooklog->debug('Webhook-Category-Url', $url);
                $response = $this->helper->postData($url);
                $this->webhooklog->debug('Webhook-Category-Url-Response', $response);
            } catch (\Exception $ex) {
                $this->webhooklog->debug('Webhook-Category-Exception', $ex->getMessage());
            }
        } else {
            $this->webhooklog->debug(
                'Webhook-Category: Webhook Feature is disabled for current store',
                ''
            );
        }
    }
}
