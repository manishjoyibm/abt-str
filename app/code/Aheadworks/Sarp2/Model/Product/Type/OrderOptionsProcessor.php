<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Product\Type;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class OrderOptionsProcessor
 * @package Aheadworks\Sarp2\Model\Product\Type
 */
class OrderOptionsProcessor
{
    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $subscriptionOptionRepository;

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param SubscriptionOptionRepositoryInterface $subscriptionOptionRepository
     * @param PlanRepositoryInterface $planRepository
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        SubscriptionOptionRepositoryInterface $subscriptionOptionRepository,
        PlanRepositoryInterface $planRepository,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->subscriptionOptionRepository = $subscriptionOptionRepository;
        $this->planRepository = $planRepository;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Process order options
     *
     * @param Product $product
     * @param array $options
     * @return array
     */
    public function process($product, &$options)
    {
        $optionId = $product->getCustomOption('aw_sarp2_subscription_type');
        if ($optionId) {
            try {
                /** @var SubscriptionOptionInterface $option */
                $option = $this->subscriptionOptionRepository->get($optionId->getValue());
                /** @var PlanInterface $plan */
                $plan = $this->planRepository->get($option->getPlanId());
                $options['aw_sarp2_subscription_plan'] = $this->dataObjectProcessor->buildOutputDataArray(
                    $plan,
                    PlanInterface::class
                );
                $optionArray = $this->dataObjectProcessor->buildOutputDataArray(
                    $option,
                    SubscriptionOptionInterface::class
                );
                unset($optionArray[SubscriptionOptionInterface::PLAN]);
                unset($optionArray[SubscriptionOptionInterface::PRODUCT]);

                $options['aw_sarp2_subscription_option'] = $optionArray;
            } catch (LocalizedException $e) {
            }
        }
        return $options;
    }
}
