<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Price
 * @package Aheadworks\Sarp2\Ui\Component\Listing\Column
 */
class Price extends Column
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceCurrency,
        array $components = [],
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $index = $this->getName();
            foreach ($dataSource['data']['items'] as & $item) {
                if ($item[$index]) {
                    $currencyCode = isset($item['base_currency_code'])
                        ? $item['base_currency_code']
                        : null;
                    $item[$index] = $this->priceCurrency->format(
                        $item[$index],
                        false,
                        null,
                        null,
                        $currencyCode
                    );
                }
            }
        }
        return $dataSource;
    }
}
