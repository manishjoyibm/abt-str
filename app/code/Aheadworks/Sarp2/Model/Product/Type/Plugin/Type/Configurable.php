<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Product\Type\Plugin\Type;

use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Product\Checker\IsSubscription;
use Aheadworks\Sarp2\Model\Product\Type\CartCandidatesProcessor;
use Aheadworks\Sarp2\Model\Product\Type\OrderOptionsProcessor;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;
use Magento\Framework\DataObject;

/**
 * Class Configurable
 * @package Aheadworks\Sarp2\Model\Product\Type\Plugin\Type
 */
class Configurable
{
    /**
     * @var IsSubscription
     */
    private $isSubscriptionChecker;

    /**
     * @var CartCandidatesProcessor
     */
    private $cartCandidatesProcessor;

    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $subscriptionOptionRepository;

    /**
     * @var OrderOptionsProcessor
     */
    private $orderOptionsProcessor;

    /**
     * @param IsSubscription $isSubscriptionChecker
     * @param CartCandidatesProcessor $cartCandidatesProcessor
     * @param SubscriptionOptionRepositoryInterface $subscriptionOptionRepository
     * @param OrderOptionsProcessor $orderOptionsProcessor
     */
    public function __construct(
        IsSubscription $isSubscriptionChecker,
        CartCandidatesProcessor $cartCandidatesProcessor,
        SubscriptionOptionRepositoryInterface $subscriptionOptionRepository,
        OrderOptionsProcessor $orderOptionsProcessor
    ) {
        $this->isSubscriptionChecker = $isSubscriptionChecker;
        $this->cartCandidatesProcessor = $cartCandidatesProcessor;
        $this->subscriptionOptionRepository = $subscriptionOptionRepository;
        $this->orderOptionsProcessor = $orderOptionsProcessor;
    }

    /**
     * @param ConfigurableProductType $subject
     * @param \Closure $proceed
     * @param Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundHasOptions(ConfigurableProductType $subject, \Closure $proceed, $product)
    {
        return $proceed($product) || $this->isSubscriptionChecker->check($product);
    }

    /**
     * @param ConfigurableProductType $subject
     * @param \Closure $proceed
     * @param Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundHasRequiredOptions(ConfigurableProductType $subject, \Closure $proceed, $product)
    {
        return $proceed($product) || $this->isSubscriptionChecker->check($product, true);
    }

    /**
     * @param ConfigurableProductType $subject
     * @param \Closure $proceed
     * @param DataObject $buyRequest
     * @param Product $product
     * @param null|string $processMode
     * @return array|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundPrepareForCartAdvanced(
        ConfigurableProductType $subject,
        \Closure $proceed,
        DataObject $buyRequest,
        $product,
        $processMode = null
    ) {
        $candidates = $proceed($buyRequest, $product, $processMode);

        if (is_array($candidates)) {
            $isSubscriptionOnly = $this->isSubscriptionChecker->check($product, true);
            $parentOptionId = (int)$buyRequest->getData('aw_sarp2_subscription_type');
            if ($parentOptionId) {
                /** @var SubscriptionOptionInterface $childOption */
                $childOption = $this->getChildOption($parentOptionId, $candidates);
                $optionId = $childOption
                    ? $childOption->getOptionId()
                    : $parentOptionId;
                $buyRequest->setData('aw_sarp2_subscription_type', $optionId);

                /** @var Product $candidate */
                foreach ($candidates as $candidate) {
                    $candidate->addCustomOption('aw_sarp2_subscription_type', $optionId)
                        ->addCustomOption('aw_sarp2_parent_subscription_type', $parentOptionId);
                }
            } elseif ($isSubscriptionOnly) {
                $candidates = $subject->getSpecifyOptionMessage()->render();
            }
        }

        return $candidates;
    }

    /**
     * Get child option
     *
     * @param int $optionId
     * @param ProductInterface[]|Product[] $candidates
     * @return SubscriptionOptionInterface|false
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getChildOption($optionId, $candidates)
    {
        /** @var SubscriptionOptionInterface $parentOption */
        $parentOption = $this->subscriptionOptionRepository->get($optionId);
        /** @var ProductInterface|Product $candidate */
        foreach ($candidates as $candidate) {
            if ($candidate->getTypeId() != ConfigurableProductType::TYPE_CODE) {
                /** @var SubscriptionOptionInterface[] $childOptions */
                $childOptions = $this->subscriptionOptionRepository->getList($candidate->getEntityId());
                foreach ($childOptions as $childOption) {
                    if ($parentOption->getPlanId() == $childOption->getPlanId()) {
                        return $childOption;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param ConfigurableProductType $subject
     * @param \Closure $proceed
     * @param Product $product
     * @return array|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetOrderOptions(
        ConfigurableProductType $subject,
        \Closure $proceed,
        $product
    ) {
        $options = $proceed($product);
        $this->orderOptionsProcessor->process($product, $options);

        return $options;
    }
}
