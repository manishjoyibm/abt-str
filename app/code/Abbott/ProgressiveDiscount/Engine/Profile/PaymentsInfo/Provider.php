<?php

namespace Abbott\ProgressiveDiscount\Engine\Profile\PaymentsInfo;

use Abbott\ProgressiveDiscount\Model\ManageMonthlySubscriptionsRepository;
use Abbott\ProgressiveDiscount\Model\Profile\ToOrder;
use Abbott\ProgressiveDiscount\Model\ResourceModel\ManageDiscountCodes\CollectionFactory;
use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterface;
use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterfaceFactory;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Profile\PaymentsInfo\ProviderInterface;
use Aheadworks\Sarp2\Engine\Profile\PaymentsInfo\Provider\StatusResolver;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Abbott\PriceInvGql\Model\Product\Subscription\PriceCalculation;
use Abbott\MyAccount\Helper\Data as AccountHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Provider implements ProviderInterface
{
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;
    /**
     * @var ToOrder
     */
    public $toOrder;
    public $logger;
    /**
     * @var ScheduledPaymentInfoInterfaceFactory
     */
    private $infoFactory;

    /**
     * @var PaymentsList
     */
    private $paymentList;

    /**
     * @var StatusResolver
     */
    private $statusResolver;

    private $mmsr;
    private $searchCriteriaBuilder;
    private $mdcr;
    public $profileRepository;
    public $priceCalculate;
    private $optionsRepository;

    /**
     * @var Emulation
     */
    protected $emulation;

    /**
     * Constructor
     *
     * @param ScheduledPaymentInfoInterfaceFactory $infoFactory
     * @param PaymentsList $paymentList
     * @param StatusResolver $statusResolver
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ManageMonthlySubscriptionsRepository $mmsr
     * @param CollectionFactory $mdcr
     * @param StoreManagerInterface $storeManager
     * @param ToOrder $toOrder
     * @param LoggerInterface $logger
     * @param ProfileRepositoryInterface $profileRepository
     * @param PriceCalculation $priceCalculate
     * @param Emulation $emulation
     * @param SubscriptionOptionRepositoryInterface $optionsRepository
     */
    public function __construct(
        ScheduledPaymentInfoInterfaceFactory $infoFactory,
        PaymentsList $paymentList,
        StatusResolver $statusResolver,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ManageMonthlySubscriptionsRepository $mmsr,
        CollectionFactory $mdcr,
        StoreManagerInterface $storeManager,
        ToOrder $toOrder,
        LoggerInterface $logger,
        ProfileRepositoryInterface $profileRepository,
        PriceCalculation $priceCalculate,
        Emulation $emulation,
        SubscriptionOptionRepositoryInterface $optionsRepository
    ) {
        $this->infoFactory = $infoFactory;
        $this->paymentList = $paymentList;
        $this->statusResolver = $statusResolver;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->mmsr = $mmsr;
        $this->mdcr = $mdcr;
        $this->storeManager = $storeManager;
        $this->toOrder = $toOrder;
        $this->logger = $logger;
        $this->profileRepository = $profileRepository;
        $this->priceCalculate = $priceCalculate;
        $this->emulation = $emulation;
        $this->optionsRepository = $optionsRepository;
    }

    /**
     * GetScheduledPaymentsInfo
     *
     * @param $profileId
     * @return ScheduledPaymentInfoInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getScheduledPaymentsInfo($profileId)
    {
        /** @var ScheduledPaymentInfoInterface $info */
        $info = $this->infoFactory->create();
        $regularProductPrice = 0;
        $total = 0;
        $totalArray = [];
        $payments = $this->paymentList->getLastScheduled($profileId);
        $profileData = $this->profileRepository->get($profileId);
        if (count($payments)) {
            /** @var PaymentInterface $payment */
            $payment = current($payments);
            if ($profileData) {
                $this->emulation->startEnvironmentEmulation($profileData->getStoreId());
                $progressiveDiscount = $this->getDiscountByMonth($profileData);
                foreach ($profileData->getItems() as $item) {
                    $regularProductPrice = $item->getRegularPrice();
                    if (empty($progressiveDiscount) && $profileData->getStoreId() !=
                        AccountHelper::GLU_STORE_ID && $profileData->getStoreId() != AccountHelper::SIM_STORE_ID) {
                        $regularPrice = $this->priceCalculate->getAutoRegularPrice(
                            $item->getProductId(),
                            $profileData->getPlanId()
                        );
                        $regularProductPrice = $this->priceCalculate
                            ->getSubscriptionCustomerGroupPrice(
                                $item->getProductId(),
                                $regularPrice,
                                $profileData->getCustomerId()
                            );
                        $discardMonthlyDiscount = $item->getProduct()->getData('is_recurring_discount');
                        if ($discardMonthlyDiscount) {
                            $regularProductPrice = $this->priceCalculate
                                ->getRecurringSubscriptionItemPrice(
                                    $item->getProductId(),
                                    $regularPrice,
                                    $profileData->getCustomerId(),
                                    $profileData->getPlanId()
                                );
                        }
                        $totalArray[] = $regularProductPrice * $item->getQty();
                    } elseif ($profileData->getStoreId() == AccountHelper::GLU_STORE_ID) {
                        $totalArray[] = $regularProductPrice * $item->getQty();
                    }
                    if ($progressiveDiscount) {
                        $regularProductPrice = $item->getRegularPrice();
                        if ($progressiveDiscount) {
                            $regularPrice = $this->priceCalculate->getAutoRegularPrice(
                                $item->getProductId(),
                                $profileData->getPlanId(),
                                $progressiveDiscount
                            );
                            $regularProductPrice = $this->priceCalculate->getSubscriptionCustomerGroupPrice(
                                $item->getProductId(),
                                $regularPrice,
                                $profileData->getCustomerId()
                            );
                        }
                        $totalArray[] = $regularProductPrice * $item->getQty();
                    }
                }
                $this->emulation->stopEnvironmentEmulation();
            }
            $searchCriteriaProfile = $this->searchCriteriaBuilder->addFilter('profile_id', $profileId, 'eq')->create();
            $monthlySubscriptions = $this->mmsr->getList($searchCriteriaProfile)->getItems();
            if (count($monthlySubscriptions) > 0 && $profileData->getStoreId() == AccountHelper::SIM_STORE_ID) {
                foreach ($monthlySubscriptions as $monthlySubscription) {
                    try {
                        $month = ($monthlySubscription->getCurrentMonth() > 8) ?
                            8 : $monthlySubscription->getCurrentMonth() + 1;
                        $searchCriteriaDiscount = $this->mdcr->create();
                        $searchCriteriaDiscount->addFieldToFilter('months', ['eq' => $month]);
                        foreach ($searchCriteriaDiscount as $discountRepo) {
                            $percent = $discountRepo->getDiscount();
                            if ($regularProductPrice > 0) {
                                $discountOnPrice = $regularProductPrice * ((100 - $percent) / 100);
                                $total = $discountOnPrice * $profileData->getItemsQty();
                            }
                        }
                    } catch (\Exception $ex) {
                        $this->logger->critical($ex->getMessage());
                    }
                }
            } else {
                $total = array_sum($totalArray);
            }
            $payment->setTotalScheduled($total);
            $payment->setBaseTotalScheduled($total);
            $info->setPaymentStatus($this->statusResolver->getInfoStatus($payment))
                ->setPaymentPeriod($payment->getPaymentPeriod())
                ->setPaymentDate(
                    $payment->getType() == PaymentInterface::TYPE_REATTEMPT
                        ? $payment->getRetryAt()
                        : $payment->getScheduledAt()
                )
                ->setAmount($payment->getTotalScheduled())
                ->setBaseAmount($payment->getBaseTotalScheduled());
        } else {
            $info->setPaymentStatus(ScheduledPaymentInfoInterface::PAYMENT_STATUS_NO_PAYMENT);
        }
        return $info;
    }

    /**
     * GetDiscountByMonth
     *
     * @param $profile
     * @return null
     */
    public function getDiscountByMonth($profile)
    {
        $discount = null;
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
                $searchCriteriaDiscount = $this->mdcr->create();
                $searchCriteriaDiscount->addFieldToFilter('months', ['eq' => $month]);
                $discountRepo = $searchCriteriaDiscount->getFirstItem();
                if ($discountRepo && $discountRepo->getDiscount()) {
                    $discount = $discountRepo->getDiscount();
                }
            }
        } catch (\Exception $ex) {
            $discount = null;
            $this->logger->critical($ex->getMessage());
        }
        return $discount;
    }
}
