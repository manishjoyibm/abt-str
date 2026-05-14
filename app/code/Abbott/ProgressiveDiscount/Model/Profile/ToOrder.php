<?php

namespace Abbott\ProgressiveDiscount\Model\Profile;

use Abbott\MyAccount\Helper\Data as AccountHelper;
use Abbott\ProgressiveDiscount\Model\ManageMonthlySubscriptionsRepository;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\Profile\Address\ToOrderAddress as AddressToOrderAddress;
use Aheadworks\Sarp2\Model\Profile\Address\ToOrder as AddressToOrder;
use Aheadworks\Sarp2\Model\Profile\Exception\CouldNotConvertException;
use Aheadworks\Sarp2\Model\Profile\Item\ToOrderItem as ItemToOrderItem;
use Aheadworks\Sarp2\Model\Profile\ToOrderPayment;
use Aheadworks\Sarp2\Model\Sales\CopySelf;
use Aheadworks\Sarp2\Model\Sales\Item\Checker\IsVirtual;
use Aheadworks\Sarp2\Model\Sales\Order\IncrementIdProvider;
use Avalara\AvaTax\Framework\Interaction\Address;
use Avalara\AvaTax\Api\RestTaxInterface;
use Avalara\AvaTax\Framework\Interaction\Tax;
use Avalara\AvaTax\Framework\Interaction\TaxCalculation;
use Avalara\AvaTax\Helper\Config;
use Avalara\AvaTax\Model\Logger\AvaTaxLogger;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
use Abbott\PriceInvGql\Model\Product\Subscription\PriceCalculation;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Framework\Api\AttributeValueFactory;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ToOrder extends \Aheadworks\Sarp2\Model\Profile\ToOrder
{
    public $logger;
    /**
     * @var \ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation
     */
    public $taxCalculation;
    /**
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Address
     */
    public $interactionAddress;
    public $interactionTax;
    /**
     * @var \ClassyLlama\AvaTax\Helper\Config
     */
    public $config;
    /**
     * @var \ClassyLlama\AvaTax\Framework\Interaction\Tax\Get\ResponseFactory
     */
    public $getTaxResponseFactory;
    public $avaTaxLogger;
    public $taxService;
    public $storeManager;
    public $profileRepository;
    /**
     * @var AddressToOrder
     */
    private $profileAddressToOrder;

    /**
     * @var AddressToOrderAddress
     */
    private $profileAddressToOrderAddress;

    /**
     * @var ItemToOrderItem
     */
    private $profileItemToOrderItem;

    /**
     * @var ToOrderPayment
     */
    private $profileToOrderPayment;

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

    private $mmsr;

    private $searchCriteriaBuilder;

    public const FEDEX_GRD_TITLE = "fedex_FEDEX_GROUND";

    public const FEDEX_SP_CODE = "fedex_SMART_POST";

    /**
     * Set's the shipping amount for recuring subscription orders
     */
    public const SHIPPING_AMMOUNT  = 0;

    public const FEDEX_GRD_DESCRIPTION = 'Federal Express - Standard Ground Shipping (Est. 3-5 business days)';

    private $mdcr;

    public $priceCalculate;

    private $productRepository;


    /**
     * @var OrderExtensionFactory
     */
    private $orderExtensionFactory;

    /**
     * @var AttributeValueFactory
     */
    private $customAttributeFactory;

    private $optionsRepository;

    public const DELIVERY_INSTRUCTION = 'packing_instruction';

    /**
     * ToOrder constructor.
     *
     * @param AddressToOrder $profileAddressToOrder
     * @param AddressToOrderAddress $profileAddressToOrderAddress
     * @param ItemToOrderItem $profileItemToOrderItem
     * @param ToOrderPayment $profileToOrderPayment
     * @param CopySelf $selfCopyService
     * @param IsVirtual $isVirtualChecker
     * @param IncrementIdProvider $incrementIdProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ManageMonthlySubscriptionsRepository $mmsr
     * @param \Abbott\ProgressiveDiscount\Model\ResourceModel\ManageDiscountCodes\CollectionFactory $mdcr
     * @param \Psr\Log\LoggerInterface $logger
     * @param TaxCalculation $taxCalculation
     * @param Address $interactionAddress
     * @param Tax $interactionTax
     * @param Config $config
     * @param Tax\Get\ResponseFactory $getTaxResponseFactory
     * @param AvaTaxLogger $avaTaxLogger
     * @param RestTaxInterface $taxService
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ProfileRepositoryInterface $profileRepository
     * @param PriceCalculation $priceCalculate
     * @param ProductRepositoryInterface $prodRepo
     * @param OrderExtensionFactory $orderExtensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     */
    public function __construct(
        AddressToOrder $profileAddressToOrder,
        AddressToOrderAddress $profileAddressToOrderAddress,
        ItemToOrderItem $profileItemToOrderItem,
        ToOrderPayment $profileToOrderPayment,
        CopySelf $selfCopyService,
        IsVirtual $isVirtualChecker,
        IncrementIdProvider $incrementIdProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ManageMonthlySubscriptionsRepository $mmsr,
        \Abbott\ProgressiveDiscount\Model\ResourceModel\ManageDiscountCodes\CollectionFactory $mdcr,
        \Psr\Log\LoggerInterface $logger,
        TaxCalculation $taxCalculation,
        Address $interactionAddress,
        Tax $interactionTax,
        Config $config,
        \Avalara\AvaTax\Framework\Interaction\Tax\Get\ResponseFactory $getTaxResponseFactory,
        AvaTaxLogger $avaTaxLogger,
        RestTaxInterface $taxService,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProfileRepositoryInterface $profileRepository,
        PriceCalculation $priceCalculate,
        ProductRepositoryInterface $prodRepo,
        OrderExtensionFactory $orderExtensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface $optionsRepository
    ) {
        $this->profileAddressToOrder = $profileAddressToOrder;
        $this->profileAddressToOrderAddress = $profileAddressToOrderAddress;
        $this->profileItemToOrderItem = $profileItemToOrderItem;
        $this->profileToOrderPayment = $profileToOrderPayment;
        $this->selfCopyService = $selfCopyService;
        $this->isVirtualChecker = $isVirtualChecker;
        $this->incrementIdProvider = $incrementIdProvider;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->mmsr = $mmsr;
        $this->mdcr = $mdcr;
        $this->logger = $logger;
        $this->taxCalculation = $taxCalculation;
        $this->interactionAddress = $interactionAddress;
        $this->interactionTax = $interactionTax;
        $this->config = $config;
        $this->getTaxResponseFactory = $getTaxResponseFactory;
        $this->avaTaxLogger = $avaTaxLogger;
        $this->taxService = $taxService;
        $this->storeManager = $storeManager;
        $this->profileRepository = $profileRepository;
        $this->priceCalculate = $priceCalculate;
        $this->productRepository = $prodRepo;
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->customAttributeFactory = $customAttributeFactory;
        $this->optionsRepository = $optionsRepository;
    }

    /**
     * Convert profile to order
     *
     * @param ProfileInterface $profile
     * @param string $paymentPeriod
     * @return OrderInterface
     * @throws CouldNotConvertException
     */
    public function convert(ProfileInterface $profile, $paymentPeriod)
    {
        $discountData = [];
        $profileData = $this->profileRepository->get($profile->getProfileId());
        if ($profile->getStoreId() != AccountHelper::SIM_STORE_ID &&
            $profile->getStoreId() != AccountHelper::GLU_STORE_ID) {
            $this->updateShippingMethod($profileData);
            $discountData = $this->getDiscountByMonth($profileData);
            $this->checkProductPrice($profile);
        }
        $isProgressive = !empty(array_filter($discountData));
        $profileBillingAddress = $profile->getBillingAddress();
        $orderBillingAddress = $this->profileAddressToOrderAddress->convert($profileBillingAddress);
        $orderAddresses = [$orderBillingAddress];
        $profileItems = $profile->getItems();
        if ($profile->getIsVirtual()
            || $paymentPeriod == PaymentInterface::PERIOD_INITIAL
        ) {
            $order = $this->profileAddressToOrder->convert($profileBillingAddress, $paymentPeriod);
        } else {
            $profileShippingAddress = $profile->getShippingAddress();
            $order = $this->profileAddressToOrder->convert($profileShippingAddress, $paymentPeriod);
            $orderShippingAddress = $this->profileAddressToOrderAddress->convert($profileShippingAddress);
            $order->setShippingAddress($orderShippingAddress);
            $orderAddresses[] = $orderShippingAddress;
        }
        $order->setBillingAddress($orderBillingAddress);
        $order->setAddresses($orderAddresses);
        $order->setPayment($this->profileToOrderPayment->convert($profile));
        /** @var OrderItem[] $orderItems */
        $orderItems = $this->convertItems($profile, $paymentPeriod);
        $order->setItems($orderItems);
        $order->setIsVirtual($this->isVirtualChecker->check($orderItems));
        /**
         * Set Delivery Instruction to subscription Order
         * ANAPOLLO-2738
         */
        if (!empty($profile->getDeliveryInstruction())) {
            $order = $this->setDeliveryInstruction($order, $profile);
        }
        $order->setIncrementId(
            $this->incrementIdProvider->getIncrementId($profile->getStoreId())
        );
        /** @var Order $order */
        $this->selfCopyService->copyByMap(
            $order,
            [
                [OrderInterface::ORDER_CURRENCY_CODE, OrderInterface::STORE_CURRENCY_CODE],
            ]
        );
        if ($isProgressive) {
            try {
                $order = $this->processProgressiveOrder($order, $profile, $discountData);
                $this->validate($order);
                return $order;
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
        $discount = 0;
        $total = 0;
        $regularTotal = 0;
        if ($profile->getStoreId() == AccountHelper::SIM_STORE_ID) {
            if ($profileData) {
                foreach ($profileData->getItems() as $item) {
                    $regularProductPrice = $item->getRegularPrice();
                }
            }
            $searchCriteriaProfile = $this->searchCriteriaBuilder
                ->addFilter(
                    'profile_id',
                    $profile->getProfileId(),
                    'eq'
                )->create();
            $monthlySubscriptions = $this->mmsr->getList($searchCriteriaProfile)->getItems();
            if (count($monthlySubscriptions) > 0) {
                foreach ($monthlySubscriptions as $monthlySubscription) {
                    try {
                        $month = ($monthlySubscription->getCurrentMonth() > 8) ?
                            8 : $monthlySubscription->getCurrentMonth();
                        $searchCriteriaDiscount = $this->mdcr->create();
                        $searchCriteriaDiscount->addFieldToFilter('months', ['eq' => $month + 1]);
                        foreach ($searchCriteriaDiscount as $discountRepo) {
                            $percent = $discountRepo->getDiscount();
                            if ($regularProductPrice > 0) {
                                $discountOnPrice = $regularProductPrice * ((100 - $percent) / 100);
                                $total = $discountOnPrice * $profileData->getItemsQty();
                                $regularTotal = $regularProductPrice * $profileData->getItemsQty();
                            }
                            $order->setSubTotal($regularTotal);
                            $extraDis = $regularTotal - $total;
                            $order->setGrandTotal($total + $order->getShippingAmount());
                            $order->setBaseGrandTotal($total + $order->getShippingAmount());
                            $order->setDiscountAmount(-$extraDis);
                            $discount = $extraDis;
                            $order->setDiscountDescription("MONTH " . ($month + 1) . " SAVINGS " . $percent . "%");
                            $monthlySubscription->setCurrentMonth($month + 1);
                            $monthlySubscription->save();
                        }
                    } catch (\Exception $ex) {
                        $this->logger->critical($ex);
                    }
                }
            }
        }
        if ($total > 0) {
            $taxIndex = 0;
            foreach ($order->getItems() as $item) {
                $item->setDiscountAmount($discount);
                $item->setBaseDiscountAmount($discount);
                $item->setBaseRowTotal($regularTotal);
                $item->setRowTotal($regularTotal);
            }
            $taxInfo = $this->getTaxDetailsForOrder($order);
            foreach ($order->getItems() as $item) {
                $taxLine = $taxInfo->getTaxLine($item->getSku());
                $lineTax = (float)$taxLine->getTax();
                $lineTaxRate = $taxInfo->getLineRate($taxLine);
                $taxpercetage = $lineTaxRate * 100;
                $item->setRowTotal($regularTotal);
                $item->setBaseTaxAmount($lineTax);
                $item->setTaxAmount($lineTax);
                $item->setTaxPercent($taxpercetage);
                $taxIndex++;
            }
            $totalTax = $taxInfo->getTotalTaxCalculated();
            $order->setTaxAmount($totalTax);
            $order->setBaseTaxAmount($totalTax);
            $order->setBaseSubTotal($regularTotal);
            $order->setSubTotal($regularTotal);
            $order->setSubTotalInclTax($regularTotal + $totalTax);
            $order->setBaseSubTotalInclTax($regularTotal + $totalTax);
            $total = $order->getGrandTotal();
            $order->setGrandTotal($total + $totalTax);
            $order->setBaseGrandTotal($total + $totalTax);
        }
        $this->validate($order);
        if ($profile->getStoreId() != AccountHelper::SIM_STORE_ID) {
            $total = 0;
            $totalTax = 0;
            $profileItemsData = $profile->getItems();
            $profileItems = $this->getProfileItems($profileItemsData);
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
            $taxInfo = $this->getTaxDetailsForOrder($order);
            foreach ($order->getItems() as $item) {
                $qty = isset($profileItems[$item->getProductId()]) ? $profileItems[$item->getProductId()] : 1;
                $total += $itemPrice = $item->getPrice() * $qty;
                $taxLine = $taxInfo->getTaxLine($item->getSku());
                $lineTax = (float)$taxLine->getTax();
                $lineTaxRate = $taxInfo->getLineRate($taxLine);
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
     * Convert profile items to order items
     *
     * @param ProfileInterface $profile
     * @param string $paymentPeriod
     * @return OrderItemInterface[]
     */
    private function convertItems(ProfileInterface $profile, $paymentPeriod)
    {
        $orderItems = [];
        foreach ($profile->getItems() as $profileItem) {
            $itemId = $profileItem->getItemId();
            if (!isset($orderItems[$itemId])) {
                $parentItemId = $profileItem->getParentItemId();
                if ($parentItemId && !isset($orderItems[$parentItemId])) {
                    $orderItems[$parentItemId] = $this->profileItemToOrderItem->convert(
                        $profileItem->getParentItem(),
                        $paymentPeriod,
                        ['parent_item' => null]
                    );
                }
                $parentItem = isset($orderItems[$parentItemId])
                ? $orderItems[$parentItemId]
                : null;
                $orderItems[$itemId] = $this->profileItemToOrderItem->convert(
                    $profileItem,
                    $paymentPeriod,
                    ['parent_item' => $parentItem]
                );
            }
        }

        return array_values($orderItems);
    }

    /**
     * Validate order entity
     *
     * @param OrderInterface|Order $order
     * @return void
     * @throws CouldNotConvertException
     */
    private function validate($order)
    {
        if (!$order->getIsVirtual() && !$order->getShippingMethod()) {
            throw new CouldNotConvertException('Unable to resolve shipping method.');
        }
    }

    public function getTaxDetailsForOrder($order)
    {
        $storeId = $this->storeManager->getStore()->getStoreId();
        $taxService = $this->taxService;
        try {
            $getTaxRequest = $this->interactionTax
                ->getGetTaxRequestForOrderObject($order);
            if (is_null($getTaxRequest)) {
                $message = __('$order was empty or address was not valid so not running getTax request.');
                throw new \Avalara\AvaTax\Exception\TaxCalculationException($message);
            }

            return $taxService->getTax($getTaxRequest, null, $storeId);
        } catch (\SoapFault $exception) {
            $message = "Exception: \n";
            if ($exception) {
                $message .= $exception->faultstring;
            }
            $message .= $taxService->__getLastRequest() . "\n";
            $message .= $taxService->__getLastResponse() . "\n";
            $this->avaTaxLogger->error(
                "Exception: \n" . ($exception) ? $exception->faultstring : "",
                [/* context */
                    'request' => var_export($taxService->__getLastRequest(), true),
                    'result' => var_export($taxService->__getLastResponse(), true),
                ]
            );
            throw new \Avalara\AvaTax\Exception\TaxCalculationException($message);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $this->avaTaxLogger->error($message);
            throw new \Avalara\AvaTax\Exception\TaxCalculationException($message);
        }
    }

    public function updateShippingMethod($profileData)
    {
        try {
            $regularShipMethod = strtolower($profileData->getRegularShippingMethod() ?? '');
            $fedexGrd = strtolower(self::FEDEX_GRD_TITLE);
            $fedexSp = strtolower(self::FEDEX_SP_CODE);
            if ($regularShipMethod != $fedexGrd && $regularShipMethod != $fedexSp) {
                $profileData->setCheckoutShippingMethod(self::FEDEX_GRD_TITLE);
                $profileData->setCheckoutShippingDescription(self::FEDEX_GRD_DESCRIPTION);
                $profileData->setTrialShippingMethod(self::FEDEX_GRD_TITLE);
                $profileData->setTrialShippingDescription(self::FEDEX_GRD_DESCRIPTION);
                $profileData->setRegularShippingMethod(self::FEDEX_GRD_TITLE);
                $profileData->setRegularShippingDescription(self::FEDEX_GRD_DESCRIPTION);
                $profileData->save();
            }
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $this->logger->critical($message);
        }
    }

    public function getProfileItems($profileItem)
    {
        $priceArray = [];
        foreach ($profileItem as $item) {
            $priceArray[$item->getProductId()] = $item->getQty();
        }

        return $priceArray;
    }

    /**
     * @param  \Aheadworks\Sarp2\Api\ProfileRepositoryInterface $profile
     * retun volid
     */
    public function checkProductPrice($profile)
    {
        $regularPrice = [];
        $profileItemPrice = [];
        $finalPrice = 0;
        try {
            foreach ($profile->getItems() as $item) {
                $regularPrice = $this->priceCalculate
                    ->getAutoRegularPrice(
                        $item->getProductId(),
                        $profile->getPlanId()
                    );
                $finalPrice =
                    $this->priceCalculate
                        ->getSubscriptionCustomerGroupPrice(
                            $item->getProductId(),
                            $regularPrice,
                            $profile->getCustomerId()
                        );
                $profileItemPrice = $item->getRegularPrice();
                $discardMonthlyDiscount = $item->getProduct()->getData('is_recurring_discount');
                if ($discardMonthlyDiscount) {
                    $finalPrice = $this->priceCalculate
                       ->getRecurringSubscriptionItemPrice(
                           $item->getProductId(),
                           $regularPrice,
                           $profile->getCustomerId(),
                           $profile->getPlanId()
                       );
                }

                if ($finalPrice && $finalPrice != $profileItemPrice) {
                    $item->setRegularPrice($finalPrice);
                    $item->setBaseRegularPrice($finalPrice);
                    $item->setRegularPriceInclTax($finalPrice);
                    $item->setBaseRegularPriceInclTax($finalPrice);
                    $item->setRegularRowTotal($finalPrice);
                    $item->setBaseRegularRowTotal($finalPrice);
                    $item->setRegularRowTotalInclTax($finalPrice);
                    $item->setBaseRegularRowTotalInclTax($finalPrice);
                    $item->save();
                }

            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->logger->critical($message);
        }
    }

    public function getDiscountByMonth($profile)
    {
        $discount = $month = $monthObj = null;
        try {
            $searchCriteriaProfile = $this->searchCriteriaBuilder->addFilter(
                'profile_id',
                $profile->getProfileId(),
                'eq'
            )->create();
            $monthlySubscriptions = $this->mmsr->getList($searchCriteriaProfile)->getItems();
            $monthlySubscription = null;
            foreach ($monthlySubscriptions as $sub) {
                $monthlySubscription = $sub;
            }
            if ($monthlySubscription && $monthlySubscription->getCurrentMonth()) {
                $month = ($monthlySubscription->getCurrentMonth() >= 8) ?
                    8 : $monthlySubscription->getCurrentMonth() + 1;
                $monthObj = $monthlySubscription;
                $searchCriteriaDiscount = $this->mdcr->create();
                $searchCriteriaDiscount->addFieldToFilter('months', ['eq' => $month]);
                $discountRepo = $searchCriteriaDiscount->getFirstItem();
                if ($discountRepo && $discountRepo->getDiscount()) {
                    $discount = $discountRepo->getDiscount();
                }
            }
        } catch (\Exception $ex) {
            $discount = $month = $monthObj = null;
            $this->logger->critical($ex->getMessage());
        }
        return [$discount, $month, $monthObj];
    }

    public function processProgressiveOrder($order, $profile, $discountData)
    {
        $progressiveDiscount = $discountData[0];
        $month = $discountData[1];
        $monthObj = $discountData[2];
        $taxIndex = 0;
        $subTotal = 0;
        $totalDiscount = 0;
        $priceAfterDiscount = 0;
        $percent = $progressiveDiscount;
        $profileItemsData = $profile->getItems();
        $profileItems = $this->getProfileItems($profileItemsData);
        $order->setShippingAmount(self::SHIPPING_AMMOUNT);
        $order->setBaseShippingAmount(self::SHIPPING_AMMOUNT);
        $order->setBaseShippingInclTax(self::SHIPPING_AMMOUNT);
        $order->setShippingInclTax(self::SHIPPING_AMMOUNT);
        foreach ($order->getItems() as $item) {
            $itemPrice = $item->getPrice();
            $regularPrice = $this->priceCalculate->getAutoRegularPrice(
                $item->getProductId(),
                $profile->getPlanId(),
                $progressiveDiscount
            );
            $regularProductPrice = $this->priceCalculate->getSubscriptionCustomerGroupPrice(
                $item->getProductId(),
                $regularPrice,
                $profile->getCustomerId()
            );
            $qty = isset($profileItems[$item->getProductId()]) ? $profileItems[$item->getProductId()] : 1;
            $discountOnPrice = $regularProductPrice * $qty;
            $regularTotal = $itemPrice * $qty;
            $discount = $regularTotal - $discountOnPrice;
            $item->setDiscountAmount($discount);
            $item->setBaseDiscountAmount($discount);
            $item->setBaseRowTotal($regularTotal);
            $item->setRowTotal($regularTotal);
            $subTotal += $regularTotal;
            $totalDiscount += $discount;
            $priceAfterDiscount += $discountOnPrice;
        }

        $taxInfo = $this->getTaxDetailsForOrder($order);
        foreach ($order->getItems() as $item) {
            $taxLine = $taxInfo->getTaxLine($item->getSku());
            $lineTax = (float)$taxLine->getTax();
            $lineTaxRate = $taxInfo->getLineRate($taxLine);
            $taxpercetage = $lineTaxRate * 100;
            $item->setBaseTaxAmount($lineTax);
            $item->setTaxAmount($lineTax);
            $item->setTaxPercent($taxpercetage);
            $taxIndex++;
        }

        $totalTax = $taxInfo->getTotalTaxCalculated();
        $order->setTaxAmount($totalTax);
        $order->setBaseTaxAmount($totalTax);
        $order->setBaseSubTotal($subTotal);
        $order->setSubTotal($subTotal);
        $order->setSubTotalInclTax($subTotal + $totalTax);
        $order->setBaseSubTotalInclTax($subTotal + $totalTax);
        $order->setGrandTotal($priceAfterDiscount + $order->getShippingAmount() + $totalTax);
        $order->setBaseGrandTotal($priceAfterDiscount + $order->getShippingAmount() + $totalTax);
        $order->setDiscountAmount(-$totalDiscount);
        $order->setDiscountDescription("MONTH " . ($month) . " SAVINGS " . $percent . "%");
        $monthObj->setCurrentMonth($month);
        $monthObj->save();
        return $order;
    }

    /**
     * @param $order
     * @param $profile
     * @return mixed
     */
    private function setDeliveryInstruction($order, $profile)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if (empty($extensionAttributes)) {
            $extensionAttributes = $this->orderExtensionFactory->create();
            $order->setExtensionAttributes($extensionAttributes);
        }
        $deliveryInstruction = $this->customAttributeFactory->create()
            ->setAttributeCode(self::DELIVERY_INSTRUCTION)
            ->setValue($profile->getDeliveryInstruction());
        $extensionAttributes->setAmastyOrderAttributes([$deliveryInstruction]);
        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }
}
