<?php

namespace Abbott\Checkout\Observer;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use \Psr\Log\LoggerInterface;
use Abbott\MyAccount\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Abbott\Checkout\Helper\Data as CheckoutData;
use Abbott\MetabolicOrdering\Model\MetabolicFactory;

class UpdateMetabolicSoldQty implements ObserverInterface
{
    protected $productRepository;

    protected $logger;

    protected $checkoutData;

    protected $metabolicModelFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        ProductRepository $productRepository,
        LoggerInterface $logger,
        StoreManagerInterface $store,
        CheckoutData $checkoutData,
        MetabolicFactory $metabolicModelFactory
    ) {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->storeManager = $store;
        $this->checkoutData = $checkoutData;
        $this->metabolicModelFactory = $metabolicModelFactory;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        if ($this->storeManager->getStore()->getCode() == Data::NEW_SIM_STORE_CODE) {
            return;
        }
        $orderItems = $observer->getOrder()->getAllItems();
        $customerEmail = $observer->getOrder()->getCustomerEmail();
        try {
            foreach ($orderItems as $item) {
                $productId = $item->getProductId();
                $productSku = $item->getSku();
                $product = $this->productRepository->getById($productId);
                $metabolicOrderingResult = $this->checkoutData->validateMetabolicOrderingProductAfterOrder(
                    $customerEmail,
                    $productSku
                );
                if (isset($metabolicOrderingResult['entity_id'])) {
                    $metabolicData = $this->metabolicModelFactory->create();
                    $metabolicDataObj = $metabolicData->load($metabolicOrderingResult['entity_id']);
                    $metabolicOrderingResult['qty'] = $metabolicOrderingResult['qty'] - $item->getQtyOrdered();
                    $metabolicDataObj->setData('qty', $metabolicOrderingResult['qty']);
                    $metabolicDataObj->save();
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
