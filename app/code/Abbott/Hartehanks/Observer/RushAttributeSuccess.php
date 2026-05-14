<?php

declare(strict_types=1);

namespace Abbott\Hartehanks\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ProductRepository;
use Abbott\Hartehanks\Model\HartehankPlaceOrderSync;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Abbott\Classfication\Model\ResourceModel\Rule\CollectionFactory as RulesCollectionFactory;
use \Magento\Quote\Model\QuoteFactory;

class RushAttributeSuccess implements ObserverInterface
{
    public $productRepository;
    public $hartehankPlaceOrder;
    public $orderCollectionFactory;
    protected $order;

    protected $oderRepository;

    protected $rulesCollectionFactory;

    protected $quoteFactory;


    public function __construct(
        Order $order,
        ProductRepository $productRepository,
        HartehankPlaceOrderSync $hartehankPlaceOrder,
        OrderCollectionFactory $orderCollectionFactory,
        RulesCollectionFactory $rulesCollectionFactory,
        QuoteFactory $quoteFactory
    ) {
         $this->order = $order;
         $this->productRepository = $productRepository;
         $this->hartehankPlaceOrder = $hartehankPlaceOrder;
         $this->orderCollectionFactory = $orderCollectionFactory;
         $this->rulesCollectionFactory = $rulesCollectionFactory;
         $this->quoteFactory = $quoteFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($observer->getEvent()->getName() == 'sales_order_save_commit_after') {
            $incrementId = $observer->getEvent()->getOrder()->getIncrementId();
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection->addFieldToFilter('increment_id', ['eq' => $incrementId]);
            $orderEntity = $orderCollection->getFirstItem();
            $payment = $orderEntity->getPayment()->getMethodInstance();
            if ($orderEntity->getStatus() == 'pending' && $payment->getCode() == 'checkmo') {
                  $orderEntity->addStatusToHistory($orderEntity->getStatus(), 'Order Placed')->save();
            }
            if ($orderEntity->getStatus() == 'processing') {
                $paymentCodes = ['purchaseorder','free'];
                if (in_array($payment->getCode(), $paymentCodes)) {
                    $orderEntity->addStatusToHistory($orderEntity->getStatus(), 'Payment Authorized')->save();
                }
                $orderId = $orderEntity->getId();
                $itemCollection = $orderEntity->getAllVisibleItems();
                $this->postOrder($orderId, $itemCollection, $orderEntity->getShippingMethod(), $orderEntity);
            }
        } else {
            $orderId = $observer->getEvent()->getOrderIds();
            $orderEntity = $this->order->load($orderId);
            if ($orderEntity->getStatus() == 'processing') {
                $itemCollection = $orderEntity->getItemsCollection();
                $this->postOrder($orderId, $itemCollection, $orderEntity->getShippingMethod(), $orderEntity);
            }
        }
    }

    public function postOrder($orderId, $itemCollection, $shippingMethod, $orderEntity = null)
    {
        $rule = $this->getCurrentOrderClassfication($orderId);
        if ($rule && $orderEntity) {
            $classfication = $rule->getOrderClassification();
            $orderEntity->setOrderClassification($classfication);
        }
        if (in_array($shippingMethod, ["fedex_STANDARD_OVERNIGHT", "fedex_FEDEX_2_DAY"])) {
            $orderEntity->setIsRushOrder(1);
        } else {
            foreach ($itemCollection as $item) {
                $productId = $item->getProductId();
                $product = $this->productRepository->getById($productId);
                if ($product->getIsRush()) {
                    $orderEntity->setIsRushOrder(1);
                    break;
                }
            }
        }
        $orderEntity->save();
    }

    public function getCurrentOrderClassfication($orderId)
    {
        try {
            $rulesCollection = $this->rulesCollectionFactory->create();
            $orderEntity = $this->order->load($orderId);
            $orderAttributesData = $this->hartehankPlaceOrder->getOrderAttributesData($orderEntity);
            $customerGroupId = $orderEntity->getCustomerGroupId();
            if ($customerGroupId == null) {
                $customerGroupId = 0;
            }
            $storeId = $orderEntity->getStoreId();
            $rulesCollection->addFieldToFilter('rule_website_ids', [
                ['finset' => [$storeId]],
            ])->addFieldToFilter('rule_customer_group', [
                ['finset' => [$customerGroupId]],
            ])->addFieldToFilter('is_active', 1)->addOrder('sort_order', 'DESC')->load();
            $quote = $this->quoteFactory->create()->load($orderEntity->getQuoteId());
            if ($quote->getIsVirtual()) {
                $address =  $quote->getBillingAddress();
            } else {
                $address = $quote->getShippingAddress();
            }
            foreach ($rulesCollection as $rule) {
                if ($rule && $rule->validate($address)) {
                    if ($rule->getRuleOrderAttribute()
                        && array_key_exists(
                            $rule->getRuleOrderAttribute(),
                            $orderAttributesData
                        )
                    ) {
                        if ($orderAttributesData[$rule->getRuleOrderAttribute()] == "No") {
                            return $rule;
                        }
                        continue;
                    }
                    return $rule;
                }
            }
            return null;
        } catch (\Exception $e) {
        }
    }
}
