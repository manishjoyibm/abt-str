<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Model\Sales\Total;

use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\DataObject\Mapper;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Populator
 * @package Aheadworks\Sarp2\Model\Sales\Total
 */
class Populator implements PopulatorInterface
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @var array
     */
    private $map = [];

    /**
     * @var array
     */
    private $amountsMap;

    /**
     * @var array
     */
    private $baseAmountsMap;

    /**
     * @var array
     */
    private $nonAmountFields = ['shipping_method', 'shipping_description', 'tax_percent'];

    /**
     * @var array
     */
    private $nonAmountsMap;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param Factory $dataObjectFactory
     * @param array $map
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        Factory $dataObjectFactory,
        array $map = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->map = array_merge($this->map, $map);
    }

    /**
     * {@inheritdoc}
     */
    public function populate(
        $entity,
        DataObject $totalsDetails,
        $currencyOption = self::CURRENCY_OPTION_CONVERT
    ) {
        $amountsMap = $this->getAmountsMap();
        $map = $currencyOption != self::CURRENCY_OPTION_USE_STORE
            ? $this->getBaseAmountsMap()
            : $amountsMap;
        Mapper::accumulateByMap($totalsDetails, $entity, $map);
        if ($currencyOption == self::CURRENCY_OPTION_CONVERT) {
            $convertedDetails = $this->convert($totalsDetails);
            Mapper::accumulateByMap($convertedDetails, $entity, $amountsMap);
        }
        $nonAmountsMap = $this->getNonAmountsMap();
        if (count($nonAmountsMap)) {
            Mapper::accumulateByMap($totalsDetails, $entity, $nonAmountsMap);
        }
    }

    /**
     * Get amounts map
     *
     * @return array
     */
    private function getAmountsMap()
    {
        if (!$this->amountsMap) {
            $this->amountsMap = array_filter(
                $this->map,
                [$this, 'isAmountField'],
                ARRAY_FILTER_USE_KEY
            );
        }
        return $this->amountsMap;
    }

    /**
     * Get base amounts map
     *
     * @return array
     */
    private function getBaseAmountsMap()
    {
        if (!$this->baseAmountsMap) {
            $map = $this->getAmountsMap();
            $closure = function ($field) {
                return 'base_' . $field;
            };
            $this->baseAmountsMap = array_map($closure, $map);
        }
        return $this->baseAmountsMap;
    }

    /**
     * Get non amounts map
     *
     * @return array
     */
    private function getNonAmountsMap()
    {
        if (!$this->nonAmountsMap) {
            $closure = function ($field) {
                return !$this->isAmountField($field);
            };
            $this->nonAmountsMap = array_filter($this->map, $closure, ARRAY_FILTER_USE_KEY);
        }
        return $this->nonAmountsMap;
    }

    /**
     * Check if specified field presents a total amount
     *
     * @param string $field
     * @return bool
     */
    private function isAmountField($field)
    {
        return !in_array($field, $this->nonAmountFields);
    }

    /**
     * Convert totals details
     *
     * @param DataObject $totalsDetails
     * @return DataObject
     */
    private function convert(DataObject $totalsDetails)
    {
        $convertedData = [];
        foreach ($totalsDetails->getData() as $key => $value) {
            if (isset($this->map[$key])) {
                $convertedData[$key] = $this->priceCurrency->convert($value);
            }
        }
        return $this->dataObjectFactory->create($convertedData);
    }
}
