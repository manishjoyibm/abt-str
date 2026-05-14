<?php

namespace Abbott\Webhook\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Abbott\Webhook\Model\ResourceModel\Webhook\CollectionFactory;
use Abbott\Webhook\Helper\CurlHelper;
use Magento\Catalog\Model\ProductFactory;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory as StoreCollectionFactory;
use Abbott\Webhook\Model\Method\Logger as WebhookLogger;

class Transport implements ObserverInterface
{
    public const CATALOG_PRD_BUNCH_DELETE = 'catalog_product_import_bunch_delete_commit_before';

    public const CATALOG_PRD_BUNCH_SAVE = 'catalog_product_import_bunch_save_after';

    public const ALL_STORE_SCOPE = 0;

    public const SKU = 'sku';

    protected $webhookFactory;

    protected $helper;

    protected $productFactory;

    protected $website;

    protected $storeCollectionFactory;

    protected $logger;

    protected $webhooklog;

    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * Transport constructor.
     * @param CollectionFactory $webhookFactory
     * @param CurlHelper $helper
     * @param ProductFactory $productFactory
     * @param StoreCollectionFactory $storeCollectionFactory
     * @param LoggerInterface $logger
     * @param WebhookLogger $webhooklog
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        CollectionFactory $webhookFactory,
        CurlHelper $helper,
        ProductFactory $productFactory,
        StoreCollectionFactory $storeCollectionFactory,
        LoggerInterface $logger,
        WebhookLogger $webhooklog,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->webhookFactory = $webhookFactory;
        $this->helper = $helper;
        $this->productFactory = $productFactory;
        $this->storeCollectionFactory = $storeCollectionFactory;
        $this->logger = $logger;
        $this->webhooklog = $webhooklog;
        $this->escaper = $escaper;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->helper->enabled()) {
            $eventname = $observer->getEvent()->getName();
            $bunches = $observer->getBunch();
            $storeViewCodes = $this->websiteMapper();
            switch ($eventname) {
                case self::CATALOG_PRD_BUNCH_SAVE:
                     $this->sendProductInfo($bunches, $storeViewCodes, $eventname);
                    break;
                case self::CATALOG_PRD_BUNCH_DELETE:
                    $this->sendDeletedSkus($bunches, $eventname);
                    break;
                default:
                    $bunches[][self::SKU] = $observer->getProduct()->getSku();
                    $this->sendProductInfo($bunches, $storeViewCodes, $eventname);
            }
        } else {
            $this->webhooklog->debug(
                'Webhook-Transport: Webhook Feature is disabled for current store',
                ''
            );
        }
    }

    /**
     * SendProductInfo
     *
     * @param $bunches
     * @param $storeViewCodes
     * @param $eventname
     * @return void
     */
    private function sendProductInfo($bunches, $storeViewCodes, $eventname)
    {
        $productskus=[];
        $currentStore = null;
        foreach ($bunches as $product) {
             $productEntity = $this->productFactory->create();
             $productEntity->load($productEntity->getIdBySku($product[self::SKU]));
             $siteIds = $productEntity->getWebsiteIds();
            /**
             * Trigger AEM webhook as per store
             * Jira ANAPOLLO-2883
             */
            if (($eventname == 'catalog_product_save_commit_after') && array_key_exists(
                'current_store_id',
                $_REQUEST['product']
            )
            ) {
                $currentStore = $this->escaper->escapeHtml($_REQUEST['product']['current_store_id']);
            }
            if ($currentStore == self::ALL_STORE_SCOPE ||
                 $eventname == self::CATALOG_PRD_BUNCH_SAVE ||
                 $eventname == self::CATALOG_PRD_BUNCH_DELETE) {
                foreach ($siteIds as $value) {
                    $productskus[$storeViewCodes[$value]][] = $product[self::SKU];
                }
            } else {
                // Should prevent triggering this product under the scope that doesn't exist
                if (in_array($currentStore, $siteIds)) {
                    $productskus[$storeViewCodes[$currentStore]][] = $product[self::SKU];
                }
            }
        }
        $this->postCurlRequest($productskus, $eventname);
    }

    /**
     * SendDeletedSkus
     *
     * @param $bunches
     * @param $eventname
     * @return void
     */
    private function sendDeletedSkus($bunches, $eventname)
    {
        $productskus=[];
        foreach ($bunches as $product) {
            $productWebsite = $product['product_websites'];
            $siteIds = explode(',', $productWebsite);
            foreach ($siteIds as $value) {
                $productskus[$value][] = $product[self::SKU];
            }
        }
        $this->postCurlRequest($productskus, $eventname);
    }

  /**
   * PostCurlRequest
   *
   * @param int $websiteId
   * @param string $eventname
   * @param string $sku
   */
    public function postCurlRequest($productskus, $eventname)
    {
        $collection = $this->webhookFactory->create();
        $result=$collection->addFieldToFilter('event_name', ['eq' => $eventname])->getFirstItem();
        foreach ($productskus as $code => $data) {
            $url = $result->getPath();
            $skus=implode(",", $data);
            $url .= "?sku=" .$skus."&storeName=".$code;
            try {
                $this->webhooklog->debug('Webhook-Url', $url);
                $response = $this->helper->postData($url);
                $this->webhooklog->debug('Webhook-Url-Response', $response);
            } catch (\Exception $ex) {
                $this->webhooklog->debug('Webhook-Exception', $ex->getMessage());
            }
        }
    }

    /**
     * WebsiteMapper
     *
     * @return array
     */
    private function websiteMapper()
    {
        $this->website=[];
        $storeCollection = $this->storeCollectionFactory->create();
        $storeCollection->addFieldToFilter('code', ['neq'=>'admin']);
        foreach ($storeCollection as $store) {
            $this->website[$store->getWebsiteId()] = $store->getCode();
        }
        return $this->website;
    }
}
