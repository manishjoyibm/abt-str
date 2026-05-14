<?php

declare(strict_types=1);

namespace Abbott\PriceInvGql\Model\Product\Subscription;

use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionPriceCalculationInterface;
use Aheadworks\Sarp2\Model\Plan\Source\PriceRounding;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Catalog\Model\ResourceModel\ProductFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class PriceCalculation implements SubscriptionPriceCalculationInterface
{
    public $customerFactory;
    public $custGroupRepository;
    public $optionsRepository;
    public $context;
    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * Construct function
     *
     * @param PlanRepositoryInterface $planRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Session $customerSession
     * @param ProductFactory $productFactory
     * @param CustomerFactory $customerFactory
     * @param GroupRepositoryInterface $custGroupRepository
     * @param SubscriptionOptionRepositoryInterface $optionsRepository
     * @param UserContextInterface $context
     */
    public function __construct(
        PlanRepositoryInterface $planRepository,
        ProductRepositoryInterface $productRepository,
        Session $customerSession,
        ProductFactory $productFactory,
        CustomerFactory $customerFactory,
        GroupRepositoryInterface $custGroupRepository,
        SubscriptionOptionRepositoryInterface $optionsRepository,
        UserContextInterface $context
    ) {
        $this->planRepository = $planRepository;
        $this->productRepository = $productRepository;
        $this->customerSession = $customerSession;
        $this->productFactory = $productFactory;
        $this->customerFactory = $customerFactory;
        $this->custGroupRepository = $custGroupRepository;
        $this->optionsRepository = $optionsRepository;
        $this->context = $context;
    }

    /**
     * GetAutoTrialPrice function
     *
     * @param int $productId
     * @param int $planId
     * @return float|int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAutoTrialPrice($productId, $planId)
    {
        $product = $this->productRepository->getById($productId);
        $plan = $this->planRepository->get($planId);
        return $this->calculateAndRound(
            $product->getPrice(),
            $plan->getTrialPricePatternPercent(),
            $plan->getPriceRounding()
        );
    }

    /**
     * GetAutoRegularPrice function
     *
     * @param int $productId
     * @param int $planId
     * @param float|int $progressiveDiscount
     * @return float|int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAutoRegularPrice($productId, $planId, $progressiveDiscount = null)
    {
        $product = $this->productRepository->getById($productId);
        $plan = $this->planRepository->get($planId);
        $discount = $plan->getRegularPricePatternPercent();
        $rounding = $plan->getPriceRounding();
        if ($progressiveDiscount !== null && $progressiveDiscount >= 0) {
            $discount = 100 - $progressiveDiscount;
            $rounding = null; //to avoid the one cent difference
        }
        return $this->calculateAndRound(
            $product->getPrice(),
            $discount,
            $rounding
        );
    }

     /**
      * GetAutoRegularPriceCustomerGroup function
      *
      * @param int $productId
      * @param int $planId
      * @param int|float $progressiveDiscount
      * @return float|int
      * @throws LocalizedException
      * @throws NoSuchEntityException
      */
    public function getAutoRegularPriceCustomerGroup($productId, $planId, $progressiveDiscount = null)
    {
        $product = $this->productRepository->getById($productId);
        $plan = $this->planRepository->get($planId);
        $customerGrpPrice = $this->getAutoRegularPriceForCustomer($productId);
        if ($customerGrpPrice) {
            return $this->calculateAndRound(
                $customerGrpPrice,
                100,
                $plan->getPriceRounding()
            );
        }
        $discount = $plan->getRegularPricePatternPercent();
        $rounding = $plan->getPriceRounding();
        if ($progressiveDiscount !== null && $progressiveDiscount >= 0) {
            $discount = 100 - $progressiveDiscount;
            $rounding = null; //to avoid the one cent difference
        }
        return $this->calculateAndRound(
            $product->getPrice(),
            $discount,
            $rounding
        );
    }

    /**
     * GetAutoRegularPriceForCustomer function
     *
     * @param int $productId
     * @param int|float $optionPrice
     * @return false|float|int|void|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAutoRegularPriceForCustomer($productId, $optionPrice = null)
    {
        $customerId = $this->context->getUserId();
        $customer = $this->customerFactory->create()->load($customerId);
        $currentGroupId = $customer->getGroupId();
        $customerGroup = $this->custGroupRepository->getById($currentGroupId)->getCode();
        $product = $this->productRepository->getById($productId);
        $productPrice = $product->getPrice();
        if ($optionPrice) {
            $productPrice = $optionPrice;
        }
        $poductReource=$this->productFactory->create();
        $attribute = $poductReource->getAttribute('subscribe_customer_group');
        if ($attribute->usesSource()) {
            $subscriptionGroups = explode(",", $product->getData('subscribe_customer_group'));
            $optionTexts = [];
            foreach ($subscriptionGroups as $subscriptionGroup) {
                $optionText = $attribute->getSource()->getOptionText($subscriptionGroup);
                if ($optionText) {
                    $optionTexts[] = $optionText;
                }
            }
            if (in_array($customerGroup, $optionTexts)) {
                if ($optionPrice) {
                    return $optionPrice;
                }
                return false;
            } else {
                foreach ($product->getTierPrices() as $tierPrice) {
                    if ((int) $tierPrice->getData('customer_group_id') == $currentGroupId) {
                        $groupPrice = (float) $tierPrice->getData('value');

                        return empty($groupPrice) ? $productPrice : $groupPrice;
                    }
                }
            }
        }
    }

    /**
     * Calculate and round subscription price
     *
     * @param float|int $price
     * @param float|int $percent
     * @param float|int $rounding
     * @return float
     */
    private function calculateAndRound($price, $percent, $rounding)
    {
        $result = $price * $percent / 100;

        $intPart = floor($result);

        if ($rounding == PriceRounding::DONT_ROUND) {
            $result =  round($result, 2);
        }

        if ($rounding == PriceRounding::UP_TO_XX_99) {
            $result = $intPart + 0.99;
        } elseif ($rounding == PriceRounding::UP_TO_XX_90) {
            $result = $intPart + 0.9;
        } elseif ($rounding == PriceRounding::DOWN_TO_XX_99) {
            if ($intPart > 1) {
                $intPart--;
            }
            $result = $intPart + 0.99;
        } elseif ($rounding == PriceRounding::DOWN_TO_XX_90) {
            if ($intPart > 1) {
                $intPart--;
            }
            $result = $intPart + 0.9;
        }

        $tens = floor($intPart / 10);
        if ($rounding == PriceRounding::UP_TO_X9_00) {
            if ($tens == 0) {
                $result = 9;
            } else {
                if (($intPart < ($tens * 10 + 9)) && ($price > ($tens * 10 + 9))) {
                    $result = $tens * 10 + 9;
                } elseif (($intPart >= ($tens * 10 + 9)) && ($price > (($tens + 1) * 10 + 9))) {
                    $result = ($tens + 1) * 10 + 9;
                } else {
                    $result = ($tens - 1) * 10 + 9;
                }
            }
        } elseif ($rounding == PriceRounding::DOWN_TO_X9_00) {
            if ($tens == 0) {
                $result = 0.01;
            } else {
                if ($result < ($tens * 10 + 9)) {
                    $result = $tens > 1 ? ($tens - 1) * 10 + 9 : 9;
                } else {
                    $result = $tens * 10 + 9;
                }
            }
        }

        return $result;
    }

    /**
     * GetSubscriptionCustomerGroupPrice function
     *
     * @param int $productId
     * @param int|float $optionPrice
     * @param int $customerId
     * @return float|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSubscriptionCustomerGroupPrice($productId, $optionPrice, $customerId)
    {
        $customer = $this->customerFactory->create()->load($customerId);
        $currentGroupId = $customer->getGroupId();
        $customerGroup = $this->custGroupRepository->getById($currentGroupId)->getCode();
        $product = $this->productRepository->getById($productId);
        $productPrice = $product->getPrice();
        if ($optionPrice) {
            $productPrice = $optionPrice;
        }
        $poductReource=$this->productFactory->create();
        $attribute = $poductReource->getAttribute('subscribe_customer_group');
        if ($attribute->usesSource()) {
            $subscriptionGroups = explode(",", $product->getData('subscribe_customer_group'));
            $optionTexts = [];
            foreach ($subscriptionGroups as $subscriptionGroup) {
                $optionText = $attribute->getSource()->getOptionText($subscriptionGroup);
                if ($optionText) {
                    $optionTexts[] = $optionText;
                }
            }
            if (in_array($customerGroup, $optionTexts)) {
                if ($optionPrice) {
                    return $optionPrice;
                }
            } else {
                foreach ($product->getTierPrices() as $tierPrice) {
                    if ((int) $tierPrice->getData('customer_group_id') == $currentGroupId) {
                        $groupPrice = (float) $tierPrice->getData('value');
                        return empty($groupPrice) ? $productPrice : $groupPrice;
                    }
                }
            }
        }
        return $productPrice;
    }

    /**
     * GetRecurringSubscriptionItemPrice function
     *
     * @param int $productId
     * @param int|float $optionPrice
     * @param int $customerId
     * @param int $planId
     * @return float|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getRecurringSubscriptionItemPrice($productId, $optionPrice, $customerId, $planId)
    {

        $customer = $this->customerFactory->create()->load($customerId);
        $currentGroupId = $customer->getGroupId();
        $product = $this->productRepository->getById($productId);
        $productPrice = $product->getPrice();
        if ($optionPrice) {
            $productPrice = $optionPrice;
        }
        $subscriptionGroups = explode(",", $product->getData('subscribe_customer_group'));
        if (in_array($currentGroupId, $subscriptionGroups)) {
            $subscriptionOptions = $this->optionsRepository->getList($productId);
            /** @var SubscriptionOptionInterface $option */
            foreach ($subscriptionOptions as $option) {
                if ($planId == $option->getPlanId()) {
                    $productPrice = $option->getIsAutoRegularPrice() ? $productPrice : $option->getRegularPrice();
                    break;
                }
            }
        } else {
            foreach ($product->getTierPrices() as $tierPrice) {
                if ((int) $tierPrice->getData('customer_group_id') == $currentGroupId) {
                    $groupPrice = (float) $tierPrice->getData('value');
                    return empty($groupPrice) ? $productPrice : $groupPrice;
                }
            }
        }
        return $productPrice;
    }
}
