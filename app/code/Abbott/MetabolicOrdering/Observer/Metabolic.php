<?php

declare(strict_types=1);

namespace Abbott\MetabolicOrdering\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Abbott\MetabolicOrdering\Helper\Data;
use Abbott\MetabolicOrdering\Model\MetabolicFactory;

class Metabolic implements ObserverInterface
{
    public $orderRepository;
    /**
     * @var MetabolicFactory
     */
    protected $metabolicModelFactory;
    
     /**
      * @var helper
      */
    protected $helper;

     /**
      * Constructor
      *
      * @param Magento\Sales\Api\OrderRepositoryInterface $orderRepository
      * @param MetabolicFactory $metabolicModelFactory
      * @param Data $helper
      */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        MetabolicFactory $metabolicModelFactory,
        Data $helper
    ) {
        $this->orderRepository = $orderRepository;
        $this->metabolicModelFactory = $metabolicModelFactory;
        $this->helper = $helper;
    }

    /**
     * To update qty back on order cancel
     *
     * @param Observer $observer
     * @return void;
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($this->helper->getModuleEnable()) {
            $request['customer_email'] = $order->getCustomerEmail();
            $orderId = $order->getEntityId();
            $orderData = $this->orderRepository->get($orderId);
            foreach ($orderData->getAllVisibleItems() as $_item) {
                $request['sku'] = $_item->getSku();
                $qtyCanceled = $_item->getQtyCanceled();
                if ($this->helper->ifExistingRecord($request)) {
                    $resultData = $this->helper->ifExistingRecord($request);
                    $metabolicData = $this->metabolicModelFactory->create();
                    $metabolicData->load($resultData['entity_id']);
                    $resultData['qty'] = $resultData['qty'] + $qtyCanceled;
                    $resultData['threthreshold_email_sent'] = 0;
                    $resultData['expiry_email_sent'] = 0;
                    $metabolicData->setData($resultData);
                    $metabolicData->save();
                    $resultData['comment'] = "qty :".$resultData['qty']." added back for sku: ".$request['sku'];
                    $this->helper->updateComments($resultData);
                }
            }
        }
    }
}
