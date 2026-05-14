<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Product\Subscription\Option;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\SubscriptionPriceCalculationInterface;
use Aheadworks\Sarp2\Model\Plan\Period\Formatter as PeriodFormatter;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Processor\CatalogPriceCalculator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Locale\Format as LocaleFormat;

/**
 * Class Formatter
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Option
 */
class Processor
{
    /**#@+
     * Constants for detailed options
     */
    const FIRST_PAYMENT = 'first_payment';
    const TRIAL_PAYMENT = 'trial_payment';
    const REGULAR_PAYMENT = 'regular_payment';
    const BILLING_CYCLE = 'billing_cycle';
    /**#@-*/

    /**
     * @var SubscriptionPriceCalculationInterface
     */
    private $priceCalculation;

    /**
     * @var PeriodFormatter
     */
    private $periodFormatter;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var LocaleFormat
     */
    private $localeFormat;

    /**
     * @var CatalogPriceCalculator
     */
    private $catalogPriceCalculator;

    /**
     * Processor constructor.
     * @param SubscriptionPriceCalculationInterface $priceCalculation
     * @param PeriodFormatter $periodFormatter
     * @param PriceCurrencyInterface $priceCurrency
     * @param LocaleFormat $localeFormat
     * @param CatalogPriceCalculator $catalogPriceCalculator
     */
    public function __construct(
        SubscriptionPriceCalculationInterface $priceCalculation,
        PeriodFormatter $periodFormatter,
        PriceCurrencyInterface $priceCurrency,
        LocaleFormat $localeFormat,
        CatalogPriceCalculator $catalogPriceCalculator
    ) {
        $this->priceCalculation = $priceCalculation;
        $this->periodFormatter = $periodFormatter;
        $this->priceCurrency = $priceCurrency;
        $this->localeFormat = $localeFormat;
        $this->catalogPriceCalculator = $catalogPriceCalculator;
    }

    /**
     * Get detailed options
     *
     * @param SubscriptionOptionInterface $option
     * @param PlanDefinitionInterface $planDefinition
     * @param bool $exclTax
     * @param bool $inclTrial
     * @return array
     */
    public function getDetailedOptions($option, $planDefinition, $exclTax = true, $inclTrial = false)
    {
        $details = [];
        if ($planDefinition->getIsInitialFeeEnabled()) {
            $details[] = [
                'label' => __('First payment'),
                'type' => self::FIRST_PAYMENT,
                'value' => $this->getFormattedFirstPayment($option, $planDefinition, $exclTax)
            ];
        }
        if ($planDefinition->getIsTrialPeriodEnabled()
            && (($inclTrial && $planDefinition->getTrialTotalBillingCycles() > 0)
                || $planDefinition->getTrialTotalBillingCycles() > 1)
        ) {
            $details[] = [
                'label' => __('Trial payment(s)'),
                'type' => self::TRIAL_PAYMENT,
                'value' => $this->getFormattedTrialPayments($option, $planDefinition, $exclTax, $inclTrial)
            ];
        }
        list($regularPaymentsFormatted, $regularPayments) = $this->getFormattedRegularPayments(
            $option,
            $planDefinition,
            $exclTax
        );
        $details[] = [
            'label' => __('Regular payments'),
            'type' => self::REGULAR_PAYMENT,
            'value' => $regularPaymentsFormatted,
            'composite_value' => $regularPayments
        ];
        $details[] = [
            'label' => __('Billing Cycle'),
            'type' => self::BILLING_CYCLE,
            'value' => $this->periodFormatter->format(
                $planDefinition->getBillingPeriod(),
                $planDefinition->getBillingFrequency()
            )
        ];
        return $details;
    }

    /**
     * Get formatted first payment
     *
     * @param SubscriptionOptionInterface $option
     * @param PlanDefinitionInterface $planDefinition
     * @param bool $exclTax
     * @return string
     */
    private function getFormattedFirstPayment($option, $planDefinition, $exclTax)
    {
        $firstPayment = $planDefinition->getIsTrialPeriodEnabled()
            ? $this->getBaseTrialPrice($option)
            : $this->getBaseRegularPrice($option);

        $firstPayment += $option->getInitialFee();

        $firstPaymentFormatted = __(
            '%1 (inc. %2 initial fee)',
            [
                $this->catalogPriceCalculator->getFormattedPrice(
                    $option->getProduct()->getId(),
                    $firstPayment,
                    $exclTax
                ),
                $this->catalogPriceCalculator->getFormattedPrice(
                    $option->getProduct()->getId(),
                    $option->getInitialFee(),
                    $exclTax
                ),
            ]
        );

        return $firstPaymentFormatted->render();
    }

    /**
     * Get formatted trial payments
     *
     * @param SubscriptionOptionInterface $option
     * @param PlanDefinitionInterface $planDefinition
     * @param bool $exclTax
     * @param bool $inclTrial
     * @return string
     */
    private function getFormattedTrialPayments($option, $planDefinition, $exclTax, $inclTrial)
    {
        $trialTotalBillingCyclesToDisplay = $planDefinition->getTrialTotalBillingCycles();
        if (!$inclTrial && $planDefinition->getIsInitialFeeEnabled()) {
            $trialTotalBillingCyclesToDisplay--;
        }

        $baseTrialPrice = $this->getBaseTrialPrice($option);
        $trialPaymentsFormatted = __(
            '%1 x %2',
            [
                $trialTotalBillingCyclesToDisplay,
                $this->catalogPriceCalculator->getFormattedPrice(
                    $option->getProduct()->getId(),
                    $baseTrialPrice,
                    $exclTax
                )
            ]
        );
        return $trialPaymentsFormatted->render();
    }

    /**
     * Get formatted regular payments
     *
     * @param SubscriptionOptionInterface $option
     * @param PlanDefinitionInterface $planDefinition
     * @param bool $exclTax
     * @return array
     */
    private function getFormattedRegularPayments($option, $planDefinition, $exclTax)
    {
        $baseRegularPrice = $this->getBaseRegularPrice($option);
        $totalBillingCyclesToDisplay = $planDefinition->getTotalBillingCycles();

        if ($planDefinition->getTotalBillingCycles() > 0) {
            if ($totalBillingCyclesToDisplay > 0
                && $planDefinition->getIsInitialFeeEnabled()
                && !$planDefinition->getIsTrialPeriodEnabled()
            ) {
                $totalBillingCyclesToDisplay--;
            }

            $regularPaymentsFormatted = __(
                '%1 x %2',
                [
                    $totalBillingCyclesToDisplay,
                    $this->catalogPriceCalculator->getFormattedPrice(
                        $option->getProduct()->getId(),
                        $baseRegularPrice,
                        $exclTax
                    )
                ]
            );
        } else {
            $regularPaymentsFormatted = __(
                '%1',
                [
                    $this->catalogPriceCalculator->getFormattedPrice(
                        $option->getProduct()->getId(),
                        $baseRegularPrice,
                        $exclTax
                    )
                ]
            );
        }
        $regularPayments = [
            'billing_cycles' => $totalBillingCyclesToDisplay,
            'value' => $this->catalogPriceCalculator->getFinalPriceAmount(
                $option->getProduct()->getId(),
                $baseRegularPrice,
                $exclTax
            )
        ];
        return [$regularPaymentsFormatted->render(), $regularPayments];
    }

    /**
     * Get option prices
     *
     * @param SubscriptionOptionInterface $option
     * @param bool $exclTax
     * @return array
     */
    public function getOptionPrices($option, $exclTax = true)
    {
        $baseRegularPrice = $this->getBaseRegularPrice($option);
        $productId = $option->getProduct()->getId();

        return [
            'oldPrice' => [
                'amount' => $this->catalogPriceCalculator->getOldPriceAmount($baseRegularPrice),
            ],
            'basePrice' => [
                'amount' => $this->catalogPriceCalculator->getBasePriceAmount($productId, $baseRegularPrice),
            ],
            'finalPrice' => [
                'amount' => $this->catalogPriceCalculator->getFinalPriceAmount($productId, $baseRegularPrice, $exclTax),
            ],
            'tierPrices' => [],
        ];
    }

    /**
     * Get product prices
     *
     * @param ProductInterface|Product $product
     * @return array
     */
    public function getProductPrices($product)
    {
        $priceInfo = $product->getPriceInfo();
        $tierPrices = [];
        $tierPricesList = $priceInfo->getPrice('tier_price')->getTierPriceList();
        foreach ($tierPricesList as $tierPrice) {
            $tierPrices[] = $tierPrice['price']->getValue();
        }
        return [
            'oldPrice' => [
                'amount' => $this->localeFormat->getNumber(
                    $priceInfo->getPrice('regular_price')->getAmount()->getValue()
                ),
            ],
            'basePrice' => [
                'amount' => $this->localeFormat->getNumber(
                    $priceInfo->getPrice('final_price')->getAmount()->getBaseAmount()
                ),
            ],
            'finalPrice' => [
                'amount' => $this->localeFormat->getNumber(
                    $priceInfo->getPrice('final_price')->getAmount()->getValue()
                ),
            ],
            'tierPrices' => $tierPrices,
        ];
    }

    /**
     * Get base trial price
     *
     * @param SubscriptionOptionInterface $option
     * @return float
     */
    private function getBaseTrialPrice($option)
    {
        $baseTrialPrice = $option->getIsAutoTrialPrice()
            ? $this->priceCalculation->getAutoTrialPrice(
                $option->getProduct()->getId(),
                $option->getPlanId()
            )
            : $option->getTrialPrice();

        return $baseTrialPrice;
    }

    /**
     * Get base regular price
     *
     * @param SubscriptionOptionInterface $option
     * @return float
     */
    private function getBaseRegularPrice($option)
    {
        $baseRegularPrice = $option->getIsAutoRegularPrice()
            ? $this->priceCalculation->getAutoRegularPrice(
                $option->getProduct()->getId(),
                $option->getPlanId()
            )
            : $option->getRegularPrice();

        return $baseRegularPrice;
    }
}
