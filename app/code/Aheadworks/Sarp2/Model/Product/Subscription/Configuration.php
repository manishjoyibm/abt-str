<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Product\Subscription;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Profile\StartDateResolver;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Processor as SubscriptionOptionProcessor;
use Aheadworks\Sarp2\Model\Product\Subscription\Configuration\OptionResolver;
use Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Class Configuration
 * @package Aheadworks\Sarp2\Model\Product\Subscription
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var StartDateResolver
     */
    private $startDateResolver;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var OptionResolver
     */
    private $optionResolver;

    /**
     * @var SubscriptionOptionProcessor
     */
    private $subscriptionOptionProcessor;

    /**
     * @var TaxHelper
     */
    private $taxHelper;

    /**
     * @param SubscriptionOptionRepositoryInterface $optionRepository
     * @param PlanRepositoryInterface $planRepository
     * @param StartDateResolver $startDateResolver
     * @param TimezoneInterface $localeDate
     * @param OptionResolver $optionResolver
     * @param SubscriptionOptionProcessor $subscriptionOptionProcessor
     * @param TaxHelper $taxHelper
     */
    public function __construct(
        SubscriptionOptionRepositoryInterface $optionRepository,
        PlanRepositoryInterface $planRepository,
        StartDateResolver $startDateResolver,
        TimezoneInterface $localeDate,
        OptionResolver $optionResolver,
        SubscriptionOptionProcessor $subscriptionOptionProcessor,
        TaxHelper $taxHelper
    ) {
        $this->optionRepository = $optionRepository;
        $this->planRepository = $planRepository;
        $this->startDateResolver = $startDateResolver;
        $this->localeDate = $localeDate;
        $this->optionResolver = $optionResolver;
        $this->subscriptionOptionProcessor = $subscriptionOptionProcessor;
        $this->taxHelper = $taxHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(ItemInterface $item)
    {
        $optionId = $item->getOptionByCode('aw_sarp2_subscription_type');
        if ($optionId && $optionId->getValue()) {
            $subscriptionOption = $this->optionRepository->get($optionId->getValue());
            $plan = $this->planRepository->get($subscriptionOption->getPlanId());
            $calculatedOption = $this->optionResolver->getSubscriptionOption($item, $plan);
            $options = $this->subscriptionOptionProcessor->getDetailedOptions(
                $calculatedOption->setOptionId($subscriptionOption->getOptionId()),
                $plan->getDefinition(),
                $this->taxHelper->displayCartPriceExclTax()
            );

            return $options;
        }
        return [];
    }

    /**
     * Get subscription start date
     * TODO: M2SARP-180 First delivery date
     *
     * @param PlanInterface $plan
     * @return string
     */
    private function getFormattedStartDate($plan)
    {
        $planDefinition = $plan->getDefinition();
        $startDate = $this->startDateResolver->getStartDate(
            $planDefinition->getStartDateType(),
            $planDefinition->getStartDateDayOfMonth()
        );
        return $this->localeDate->formatDateTime(
            new \DateTime($startDate),
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE
        );
    }
}
