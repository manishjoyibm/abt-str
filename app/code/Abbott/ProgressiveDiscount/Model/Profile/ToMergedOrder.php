<?php


namespace Abbott\ProgressiveDiscount\Model\Profile;

use Abbott\ProgressiveDiscount\Model\ResourceModel\ManageDiscountCodes\CollectionFactory;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Profile\PaymentInfoInterface;
use Aheadworks\Sarp2\Model\Profile\Address\ToOrderAddress as AddressToOrderAddress;
use Aheadworks\Sarp2\Model\Profile\Item\Merger;
use Aheadworks\Sarp2\Model\Profile\Merged\Address\ToOrder as AddressToOrder;
use Aheadworks\Sarp2\Model\Profile\Merged\Item\ToOrderItem as ItemToOrderItem;
use Aheadworks\Sarp2\Model\Profile\Merged\Set\DataResolver;
use Aheadworks\Sarp2\Model\Sales\CopySelf;
use Aheadworks\Sarp2\Model\Sales\Item\Checker\IsVirtual;
use Aheadworks\Sarp2\Model\Sales\Order\IncrementIdProvider;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\CollectorList;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\Subject;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\SubjectFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Abbott\ProgressiveDiscount\Model\ManageMonthlySubscriptionsRepository;
use Abbott\ProgressiveDiscount\Model\Profile\ToOrder;
use Abbott\MyAccount\Helper\Data as AccountHelper;
use Aheadworks\Sarp2\Model\Profile\ToOrderPayment;
use Psr\Log\LoggerInterface;

class ToMergedOrder extends \Aheadworks\Sarp2\Model\Profile\ToMergedOrder
{
    /**
     * Set's the shipping amount for recuring subscription orders
     */
    public const SHIPPING_AMMOUNT  = 0;

    /**
     * @var AddressToOrderAddress
     */
    private $profileAddressToOrderAddress;

    /**
     * @var AddressToOrder
     */
    private $profileAddressToOrder;

    /**
     * @var ItemToOrderItem
     */
    private $profileItemToOrderItem;

    /**
     * @var ToOrderPayment
     */
    private $profileToOrderPayment;

    /**
     * @var DataResolver
     */
    private $dataResolver;

    /**
     * @var Merger
     */
    private $itemsMerger;

    /**
     * @var CollectorList
     */
    private $collectorList;

    /**
     * @var SubjectFactory
     */
    private $collectorSubjectFactory;

    /**
     * @var CopySelf
     */
    private $selfCopyService;

    /**
     * @var IsVirtual
     */
    private $isVirtualChecker;

    /**
     * @var IncrementIdProvider
     */
    private $incrementIdProvider;

    private $searchCriteriaBuilder;

    private $mmsr;

    private $mdcr;

    private $toOrder;

    private $logger;

    /**
     * Constructor
     *
     * @param AddressToOrderAddress $profileAddressToOrderAddress
     * @param AddressToOrder $profileAddressToOrder
     * @param ItemToOrderItem $profileItemToOrderItem
     * @param ToOrderPayment $profileToOrderPayment
     * @param DataResolver $dataResolver
     * @param Merger $itemsMerger
     * @param CollectorList $collectorList
     * @param SubjectFactory $collectorSubjectFactory
     * @param CopySelf $selfCopyService
     * @param IsVirtual $isVirtualChecker
     * @param IncrementIdProvider $incrementIdProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ManageMonthlySubscriptionsRepository $mmsr
     * @param CollectionFactory $mdcr
     * @param \Abbott\ProgressiveDiscount\Model\Profile\ToOrder $toOrder
     * @param LoggerInterface $logger
     */
    public function __construct(
        AddressToOrderAddress $profileAddressToOrderAddress,
        AddressToOrder $profileAddressToOrder,
        ItemToOrderItem $profileItemToOrderItem,
        ToOrderPayment $profileToOrderPayment,
        DataResolver $dataResolver,
        Merger $itemsMerger,
        CollectorList $collectorList,
        SubjectFactory $collectorSubjectFactory,
        CopySelf $selfCopyService,
        IsVirtual $isVirtualChecker,
        IncrementIdProvider $incrementIdProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ManageMonthlySubscriptionsRepository $mmsr,
        CollectionFactory $mdcr,
        ToOrder $toOrder,
        LoggerInterface $logger
    ) {
        $this->profileAddressToOrderAddress = $profileAddressToOrderAddress;
        $this->profileAddressToOrder = $profileAddressToOrder;
        $this->profileItemToOrderItem = $profileItemToOrderItem;
        $this->profileToOrderPayment = $profileToOrderPayment;
        $this->dataResolver = $dataResolver;
        $this->itemsMerger = $itemsMerger;
        $this->collectorList = $collectorList;
        $this->collectorSubjectFactory = $collectorSubjectFactory;
        $this->selfCopyService = $selfCopyService;
        $this->isVirtualChecker = $isVirtualChecker;
        $this->incrementIdProvider = $incrementIdProvider;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->mmsr = $mmsr;
        $this->mdcr = $mdcr;
        $this->toOrder = $toOrder;
        $this->logger = $logger;
    }

    /**
     * Convert profiles to merged order
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return OrderInterface
     */
    public function convert($paymentsInfo)
    {
        $firstProfile = $paymentsInfo[0]->getProfile();
        $profileBillingAddress = $firstProfile->getBillingAddress();
        $orderBillingAddress = $this->profileAddressToOrderAddress->convert($profileBillingAddress);
        $orderAddresses = [$orderBillingAddress];
        if ($this->dataResolver->isVirtual($paymentsInfo)
            || $this->dataResolver->getPaymentPeriod($paymentsInfo) == PaymentInterface::PERIOD_INITIAL
        ) {
            $order = $this->profileAddressToOrder->convert($profileBillingAddress);
        } else {
            $profileShippingAddress = $firstProfile->getShippingAddress();
            $order = $this->profileAddressToOrder->convert($profileShippingAddress);
            $orderShippingAddress = $this->profileAddressToOrderAddress->convert($profileShippingAddress);
            $order->setShippingAddress($orderShippingAddress);
            $orderAddresses[] = $orderShippingAddress;
        }
        $order->setBillingAddress($orderBillingAddress);
        $order->setAddresses($orderAddresses);
        $order->setPayment($this->profileToOrderPayment->convert($firstProfile));
        /** @var OrderItem[] $orderItems */
        $orderItems = $this->convertItems($paymentsInfo, $order);
        $order->setItems($orderItems);
        $order->setIsVirtual($this->isVirtualChecker->check($orderItems));
        $storeId = $this->dataResolver->getStoreId($paymentsInfo);
        $order->setIncrementId($this->incrementIdProvider->getIncrementId($storeId));
        /** @var Order $order */
        $this->selfCopyService->copyByMap(
            $order,
            [
                [OrderInterface::ORDER_CURRENCY_CODE, OrderInterface::STORE_CURRENCY_CODE],
            ]
        );
        if ($firstProfile->getStoreId() == AccountHelper::ABT_STORE_ID) {
            $total = 0;
            $totalTax = 0;
            $profileItems = $this->getMergedProfileItems($paymentsInfo);
            $taxIndex = 0;
            $order->setShippingAmount(self::SHIPPING_AMMOUNT);
            $order->setBaseShippingAmount(self::SHIPPING_AMMOUNT);
            $order->setBaseShippingInclTax(self::SHIPPING_AMMOUNT);
            $order->setShippingInclTax(self::SHIPPING_AMMOUNT);
            foreach ($order->getItems() as $item) {
                $qty = isset($profileItems[$item->getProductId()]) ? $profileItems[$item->getProductId()] : 1;
                $itemPrice = $item->getPrice() * $qty;
                $item->setBaseRowTotal($itemPrice);
                $item->setRowTotal($itemPrice);
            }
            $taxInfo = $this->toOrder->getTaxDetailsForOrder($order);
            foreach ($order->getItems() as $item) {
                $qty = isset($profileItems[$item->getProductId()]) ? $profileItems[$item->getProductId()] : 1;
                $total += $itemPrice = $item->getPrice() * $qty;
                $lineTax = $taxInfo->getTaxLines()[$taxIndex]->getTax();
                $lineTaxRate = $taxInfo->getTaxLines()[$taxIndex]->getRate();
                $taxpercetage = $lineTaxRate * 100;
                $item->setBaseTaxAmount($lineTax);
                $item->setTaxAmount($lineTax);
                $item->setTaxPercent($taxpercetage);
                $item->setRowTotal($itemPrice);
                $taxIndex++;
            }
            $totalTax = $taxInfo->getTotalTaxCalculated();
            $order->setSubTotal($total);
            $order->setBaseSubTotal($total);
            $order->setSubTotalInclTax($total + $totalTax);
            $order->setBaseSubTotalInclTax($total + $totalTax);
            $order->setTaxAmount($totalTax);
            $order->setBaseTaxAmount($totalTax);
            $order->setGrandTotal($total + $totalTax);
            $order->setBaseGrandTotal($total + $totalTax);
        }
        return $order;
    }

    /**
     * Convert profile items to merged order items
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @param OrderInterface $order
     * @return OrderItemInterface[]
     */
    private function convertItems($paymentsInfo, $order)
    {
        $pairs = [];
        $merged = $this->itemsMerger->mergeItems($paymentsInfo);
        foreach ($merged as $mergedItem) {
            $profileItem = $mergedItem->getItem();
            $itemId = $profileItem->getItemId();
            $paymentPeriod = $mergedItem->getPaymentPeriod();
            if (!isset($pairs[$itemId])) {
                $parentItemId = $profileItem->getParentItemId();
                if ($parentItemId && !isset($pairs[$parentItemId])) {
                    $pairs[$parentItemId] = [
                        $mergedItem,
                        $this->profileItemToOrderItem->convert(
                            $profileItem->getParentItem(),
                            $paymentPeriod,
                            ['parent_item' => null]
                        ),
                    ];
                }
                $parentItem = isset($pairs[$parentItemId])
                ? $pairs[$parentItemId][1]
                : null;
                $pairs[$itemId] = [
                    $mergedItem,
                    $this->profileItemToOrderItem->convert(
                        $profileItem,
                        $paymentPeriod,
                        ['parent_item' => $parentItem]
                    ),
                ];
            }
        }
        /** @var Subject $collectSubject */
        $collectSubject = $this->collectorSubjectFactory->create(
            [
                'paymentsInfo' => $paymentsInfo,
                'order' => $order,
                'itemPairs' => $pairs,
            ]
        );
        foreach ($this->collectorList->getCollectors() as $totalCollector) {
            $totalCollector->collect($collectSubject);
        }
        /**
         * @param array $pair
         * @return OrderItemInterface
         */
        $closure = function ($pair) {
            return $pair[1];
        };
        return array_map($closure, $pairs);
    }

    /**
     * GetMergedProfileItems
     *
     * @param $paymentsInfo
     * @return array
     */
    public function getMergedProfileItems($paymentsInfo)
    {
        $priceArray = [];
        foreach ($paymentsInfo as $profileData) {
            $profile = $profileData->getProfile();
            $profileItems = $profile->getItems();
            foreach ($profileItems as $item) {
                $priceArray[$item->getProductId()] = $item->getQty();
            }
        }
        return $priceArray;
    }
}
