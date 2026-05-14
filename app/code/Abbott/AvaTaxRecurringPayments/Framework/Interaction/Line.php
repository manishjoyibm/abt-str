<?php

/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Abbott\AvaTaxRecurringPayments\Framework\Interaction;

use Avalara\AvaTax\Framework\Interaction\Line as AvaTaxLine;

use Magento\Framework\DataObjectFactory;
use Avalara\AvaTax\Framework\Interaction\MetaData\MetaDataObjectFactory;
use Avalara\AvaTax\Helper\Config;
use Avalara\AvaTax\Framework\Interaction\MetaData\ValidationException;
use Avalara\AvaTax\Helper\CustomsConfig;



class Line extends AvaTaxLine
{
    /**
     * Tax calculation model
     *
     * @var \Magento\Tax\Model\Calculation
     */
    protected $calculationTool;

    private $productFactory;

    public function __construct(
        Config $config,
        \Avalara\AvaTax\Helper\TaxClass $taxClassHelper,
        \Avalara\AvaTax\Model\Logger\AvaTaxLogger $avaTaxLogger,
        MetaDataObjectFactory $metaDataObjectFactory,
        DataObjectFactory $dataObjectFactory,
        CustomsConfig $customsConfigHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Tax\Model\Calculation $calculationTool
    ) {

        $this->config = $config;
        $this->taxClassHelper = $taxClassHelper;
        $this->avaTaxLogger = $avaTaxLogger;
        $this->metaDataObject = $metaDataObjectFactory->create(['metaDataProperties' => $this::$validFields]);
        $this->dataObjectFactory = $dataObjectFactory;
        $this->customsConfigHelper = $customsConfigHelper;
        $this->productFactory = $productFactory;
        parent::__construct(
            $config,
            $taxClassHelper,
            $avaTaxLogger,
            $metaDataObjectFactory,
            $dataObjectFactory,
            $customsConfigHelper,
            $calculationTool
        );
    }


    /**
     * Convert \Magento\Tax\Model\Sales\Quote\ItemDetails to an array to be used for building a line object
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface $item
     * @return \Magento\Framework\DataObject
     */
    protected function convertOrderItemToData(\Magento\Sales\Api\Data\OrderItemInterface $item)
    {
         $amount = $item->getBaseRowTotal() - $item->getBaseDiscountAmount();

        if ($item->getQtyOrdered() == 0) {
            return false;
        }

        $storeId = $item->getStoreId();
        $product = $this->productFactory->create()->load($item->getProductId());
        $itemData = $this->buildItemData($product, $storeId);

        if (!$itemData['itemCode']) {
            $itemData['itemCode'] = $item->getSku();
        }

        $data = [
            'mage_sequence_no' => $item->getSku(),
            'item_code' =>$itemData['itemCode'],
            'tax_code' => $itemData['taxCode'],
            'description' => $item->getName(),
            'quantity' => $item->getQtyOrdered(),
            'amount' => $amount,
            'tax_included' => false

        ];


        /** @var \Magento\Framework\DataObject $line */
        $line = $this->dataObjectFactory->create(['data' => $data]);

        return $line;
    }

    /**
     * Get tax line object
     *
     * @param $data
     * @return \Magento\Framework\DataObject|null|bool
     */
    public function getLine($data)
    {
        /** @var \Magento\Framework\DataObject $line */
        $line = false;
        switch (true) {
            case ($data instanceof \Magento\Sales\Api\Data\OrderItemInterface):
                $line = $this->convertOrderItemToData($data);
                break;
            case ($data instanceof \Magento\Tax\Api\Data\QuoteDetailsItemInterface):
                $line = $this->convertTaxQuoteDetailsItemToData($data);
                break;
            case ($data instanceof \Magento\Sales\Api\Data\InvoiceItemInterface):
                $line = $this->convertInvoiceItemToData($data);
                break;
            case ($data instanceof \Magento\Sales\Api\Data\CreditmemoItemInterface):
                $line = $this->convertCreditMemoItemToData($data);
                break;
            case (!is_array($data)):
                return false;
                break;
        }

        if (!$line) {
            return null;
        }

        try {
            $validatedData = $this->metaDataObject->validateData($line->getData());
            $line->setData($validatedData);
        } catch (ValidationException $e) {
            $this->avaTaxLogger->error('Error validating line: ' . $e->getMessage(), [
                'data' => var_export($line->getData(), true)
            ]);
        }

        return $line;
    }

    /**
     * Accepts an invoice or creditmemo and returns an \AvaTax\Line object
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $data
     * @param $order
     * @return \AvaTax\Line|bool
     * @throws MetaData\ValidationException
     */
    public function getShippingLineOrder(\Magento\Sales\Api\Data\OrderInterface $data)
    {
        $shippingAmount = $data->getBaseShippingAmount();
        $discounted = false;

        // If shipping rate doesn't have cost associated with it, do nothing
        if ($shippingAmount <= 0) {
            return false;
        }

        // Check the order to see if a shipping discount amount exists
        // and the shipping amount on the invoice|creditmemo matches the shipping amount on the order
        // then subtract the discount amount from the shipping amount and if 0 return false
        $shippingDiscountAmount = $data->getShippingDiscountAmount();
        $orderShippingAmount = $data->getShippingAmount();
        if (
            $shippingDiscountAmount > 0
            && $shippingAmount == $orderShippingAmount
            && $shippingAmount - $shippingDiscountAmount >= 0
        ) {
            $shippingAmount = $shippingAmount - $shippingDiscountAmount;
            $discounted = true;
        }

        $storeId = $data->getStoreId();
        $itemCode = $this->config->getSkuShipping($storeId);
        $data = [
            'No' => $this->getLineNumber(),
            'ItemCode' => $itemCode,
            'TaxCode' => $this->taxClassHelper->getAvataxTaxCodeForShipping(),
            'Description' => self::SHIPPING_LINE_DESCRIPTION,
            'Qty' => 1,
            'Amount' => $shippingAmount,
            'Discounted' => $discounted,
        ];

        $line = $this->dataObjectFactory->create(['data' => $data]);

        try {
            $validatedData = $this->metaDataObject->validateData($line->getData());
            $line->setData($validatedData);
        } catch (ValidationException $e) {
            $this->avaTaxLogger->error('Error validating line: ' . $e->getMessage(), [
                'data' => var_export($line->getData(), true)
            ]);
            throw $e;
        }

        return $line;
    }
}
