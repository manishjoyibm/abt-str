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

use AvaTax\DetailLevel;
use Avalara\DocumentType;
use AvaTax\GetTaxRequest;
use Avalara\AvaTax\Framework\Interaction\MetaData\ValidationException;
use Avalara\AvaTax\Framework\Interaction\Tax as ClassyAvaTax;
use Avalara\AvaTax\Helper\Config;

/**
 * Class Tax
 */
class Tax extends ClassyAvaTax
{

    /**
     * A list of valid fields for the data array and meta data about their types to use in validation
     * based on the API documentation.  If any fields are added or removed, the same should be done in getTaxRequest.
     *
     * @var array
     */
    public static $validFields = [
        'store_id' => ['type' => 'integer'],
        'business_identification_no' => ['type' => 'string', 'length' => 25],
        'commit' => ['type' => 'boolean'],
        // Company Code is not required by the the API, but we are requiring it in this integration
        'company_code' => ['type' => 'string', 'length' => 25, 'required' => true],
        'currency_code' => ['type' => 'string', 'length' => 3],
        'customer_code' => ['type' => 'string', 'length' => 50, 'required' => true],
        'entity_use_code' => ['type' => 'string', 'length' => 25],
        'discount' => ['type' => 'double'],
        'code' => ['type' => 'string', 'length' => 50],
        'date' => ['type' => 'string', 'format' => '/\d\d\d\d-\d\d-\d\d/'], // REST TransactionBuilder always uses current date
        'type' => [
            'type' => 'string',
            'options' =>
                ['SalesOrder', 'SalesInvoice', 'PurchaseOrder', 'PurchaseInvoice', 'ReturnOrder', 'ReturnInvoice',
                    "". DocumentType::C_ANY, "".DocumentType::C_SALESORDER, "".DocumentType::C_SALESINVOICE,
                    "".DocumentType::C_PURCHASEORDER, "".DocumentType::C_PURCHASEINVOICE,
                    "".DocumentType::C_RETURNORDER, "".DocumentType::C_RETURNINVOICE,
                    "".DocumentType::C_INVENTORYTRANSFERORDER, "".DocumentType::C_INVENTORYTRANSFERINVOICE,
                    "".DocumentType::C_REVERSECHARGEORDER, "".DocumentType::C_REVERSECHARGEINVOICE],
            'required' => true,
        ],
        'exchange_rate' => ['type' => 'double'],
        'exchange_rate_effective_date' => ['type' => 'string', 'format' => '/\d\d\d\d-\d\d-\d\d/'],
        'lines' => [
            'type' => 'array',
            'length' => 15000,
            'subtype' => ['*' => ['type' => 'dataObject', 'class' => '\Magento\Framework\DataObject']],
            'required' => true,
        ],
        'addresses' => [
            'type' => 'array',
            'subtype' => ['*' => ['type' => 'dataObject', 'class' => '\Magento\Framework\DataObject']],
            'required' => true,
        ],
        'reporting_location_code' => ['type' => 'string', 'length' => 50],
        'exemption_no' => ['type' => 'string', 'length' => 50],
        'purchase_order_no' => ['type' => 'string', 'length' => 50],
        'reference_code' => ['type' => 'string', 'length' => 50],
        'tax_override' => ['type' => 'dataObject', 'class' => '\Magento\Framework\DataObject'],
        'is_seller_importer_of_record' => ['type' => 'boolean'],
        'shipping_mode' => ['type' => 'string']
    ];

    /**
     * Convert Tax Quote Details into data to be converted to a GetTax Request
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $taxQuoteDetails
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Framework\DataObject|null
     * @throws ValidationException
     * @throws LocalizedException
     */
    protected function convertTaxQuoteDetailsToRequest(
        \Magento\Tax\Api\Data\QuoteDetailsInterface $taxQuoteDetails,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Api\Data\CartInterface $quote
    ) {
        $lines = [];
        $certificate_no = null;
        $customer = $quote->getCustomer();
        $customerData = $this->getCustomerById($customer->getId());
        if ($customerData) {
            $tax_cert_no = ($customerData->getCustomAttribute('tax_exempt_number')) ? $customerData->getCustomAttribute('tax_exempt_number')->getValue() : null;
            $tax_cert_file = ($customerData->getCustomAttribute('tax_exempt_file')) ? $customerData->getCustomAttribute('tax_exempt_file')->getValue() : null;
            $tax_cert_date = ($customerData->getCustomAttribute('tax_certificate_date')) ? $customerData->getCustomAttribute('tax_certificate_date')->getValue() : null;
            if ($tax_cert_no && $tax_cert_date && $tax_cert_file) {
                $date = date("Y-m-d");
                $currentDate = $this->getFormattedDate($quote->getStoreId(), $date);
                $expiryDate = $this->getFormattedDate($quote->getStoreId(), $tax_cert_date);
                $diff = strtotime($expiryDate) - strtotime($currentDate);
                if ($diff >= 0) {
                    $certificate_no = $tax_cert_no;
                }
            }
        }
        $items = $taxQuoteDetails->getItems();
        $keyedItems = $this->taxCalculation->getKeyedItems($items);
        $childrenItems = $this->taxCalculation->getChildrenItems($items);

        /** @var \Magento\Tax\Api\Data\QuoteDetailsItemInterface $item */
        foreach ($keyedItems as $item) {
            /**
             * If a quote has children and they are calculated (e.g., Bundled products with dynamic pricing)
             * @see \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::mapItems
             * then we only need to pass child items to AvaTax. Due to the logic in
             * @see \ClassyLlama\AvaTax\Framework\Interaction\TaxCalculation::calculateTaxDetails
             * the parent tax gets calculated based on children items
             */
            //
            if (isset($childrenItems[$item->getCode()])) {
                /** @var \Magento\Tax\Api\Data\QuoteDetailsItemInterface $childItem */
                foreach ($childrenItems[$item->getCode()] as $childItem) {
                    $line = $this->interactionLine->getLine($childItem);
                    if ($line) {
                        $lines[] = $line;
                    }
                }
            } else {
                $line = $this->interactionLine->getLine($item);
                if ($line) {

                    /**
                     * The Magento Core does not have the necessary details in the QuoteDetailsItem
                     * which are returned from the call to getItems() above in order to determine if
                     * the shipping type item has a discount or not as it is built differently than other
                     * product type items that include a discountAmount with the item. We can however
                     * determine this by examining the ShipmentAssignment that happens to store the
                     * details of the shipping calculation that occurred earlier in other collect totals.
                     */

                    // Check if we should adjust for a shipping discount amount
                    if ($this->isShippingDiscountAmountAdjustmentNeeded($shippingAssignment, $item, $line)) {

                        // Get the shipping discount amount from the address
                        $shippingDiscountAmount = $shippingAssignment->getShipping()->getAddress()->getShippingDiscountAmount();

                        // Recalculate the line amount with the shipping discount amount included
                        $amountAfterDiscount = ($item->getUnitPrice() * $item->getQuantity()) - $shippingDiscountAmount;

                        // Adjust the line amount
                        $line->setAmount($amountAfterDiscount);
                    }

                    $lines[] = $line;
                }
            }
        }

        // Shipping Address not documented in the interface for some reason
        // they do have a constant for it but not a method in the interface
        //
        // If quote is virtual, getShipping will return billing address, so no need to check if quote is virtual
        $shippingAddress = $shippingAssignment->getShipping();
        $address = $this->address->getAddress($shippingAddress->getAddress());

        $store = $this->storeRepository->getById($quote->getStoreId());
        $currentDate = $this->getFormattedDate($store);

        $customerUsageType = $quote->getCustomer()
            ? $this->taxClassHelper->getAvataxTaxCodeForCustomer($quote->getCustomer())
            : null;
        $data = [
            'store_id' => $store->getId(),
            'commit' => false, // quotes should never be committed
            'currency_code' => $quote->getCurrency()->getQuoteCurrencyCode(),
            'customer_code' => $this->customer->getCustomerCodeByCustomerId(
                $quote->getCustomerId(),
                $quote->getId(),
                $quote->getStoreId()
            ),
            'entity_use_code' => $customerUsageType,
            'addresses' => [
                $this->restConfig->getAddrTypeTo() => $address,
            ],
            'code' => self::AVATAX_DOC_CODE_PREFIX . $quote->getId(),
            'type' => $this->restConfig->getDocTypeQuote(),
            'exchange_rate' => $this->getExchangeRate(
                $store,
                $quote->getCurrency()->getBaseCurrencyCode(),
                $quote->getCurrency()->getQuoteCurrencyCode()
            ),
            'exchange_rate_effective_date' => $currentDate,
            'lines' => $lines,
            'purchase_order_no' => $quote->getReservedOrderId(),
            'exemption_no' => $certificate_no,
            /*'shipping_mode' => $this->customsConfig->getShippingTypeForMethod(
                $shippingAddress->getMethod(),
                $quote->getStoreId()
            )*/
        ];
        /** @var \Magento\Framework\DataObject $request */
        $request = $this->dataObjectFactory->create(['data' => $data]);

        return $request;
    }


    protected function populateLine(array $data, \AvaTax\Line $line)
    {
        // Set any data elements that exist on the getTaxRequest
        foreach ($data as $key => $datum) {
            $methodName = 'set' . $key;
            if (method_exists($line, $methodName)) {
                $line->$methodName($datum);
            }
        }
        return $line;
    }


    /**
     * Creates and returns a populated tax request for a invoice
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface|\Magento\Sales\Api\Data\CreditmemoInterface $object
     * @return \Magento\Framework\DataObject
     * @throws ValidationException
     * @throws LocalizedException
     */
    public function getGetTaxRequestForOrderObject($order)
    {
        // Create an array of items for the order being processed
        $orderItems = $order->getAllItems();
        $customerData = $this->getCustomerById($order->getCustomerId());
        $certificate_no = null;
        if ($customerData) {
            $tax_cert_no = ($customerData->getCustomAttribute('tax_exempt_number')) ? $customerData->getCustomAttribute('tax_exempt_number')->getValue() : null;
            $tax_cert_file = ($customerData->getCustomAttribute('tax_exempt_file')) ? $customerData->getCustomAttribute('tax_exempt_file')->getValue() : null;
            $tax_cert_date = ($customerData->getCustomAttribute('tax_certificate_date')) ? $customerData->getCustomAttribute('tax_certificate_date')->getValue() : null;
            if ($tax_cert_no && $tax_cert_date && $tax_cert_file) {
                $date = date("Y-m-d");
                $currentDate = $this->getFormattedDate($order->getStoreId(), $date);
                $expiryDate = $this->getFormattedDate($order->getStoreId(), $tax_cert_date);
                $diff = strtotime($expiryDate) - strtotime($currentDate);
                if ($diff >= 0) {
                    $certificate_no = $tax_cert_no;
                }
            }
        }
        foreach ($orderItems as $item) {
            if (!$this->isProductCalculated($item)) {
                // Don't add configurable products to the array
                $orderItemsArray[$item->getProductID()] = $item;
            }
        }

        $lines = [];
        $items = $order->getItems();

        //$this->taxClassHelper->populateCorrectTaxClasses($items, $order->getStoreId());
        /** @var \Magento\Tax\Api\Data\QuoteDetailsItemInterface $item */
        foreach ($items as $item) {
            // Only add this item if it is in the order items array
            if (isset($orderItemsArray[$item->getProductId()])) {
                $line = $this->interactionLine->getLine($item);
                if ($line) {
                    $amount = ($item->getQtyOrdered() * $item->getPrice()) - $item->getDiscountAmount();
                    $line->setAmount($amount);
                    $lines[] = $line;
                }
            }
        }

        /** @var \Magento\Sales\Api\Data\OrderAddressInterface $address */
        if (!$order->getIsVirtual()) {
            $address = $order->getShippingAddress();
        } else {
            $address = $order->getBillingAddress();
        }
        $avaTaxAddress = $this->address->getAddress($address);

        $store = $this->storeRepository->getById($order->getStoreId());

        $currentDate = $this->getFormattedDate($store, $order->getCreatedAt());

        $docType = null;
        $taxCalculationDate = null;
        if ($order instanceof \Magento\Sales\Api\Data\InvoiceInterface) {
            $docType = $this->restConfig->getDocTypeInvoice();

            if ($this->areTimesDifferentDays($order->getCreatedAt(), $order->getCreatedAt(), $order->getStoreId())) {
                $taxCalculationDate = $this->getFormattedDate($store, $order->getCreatedAt());
            }
        } else {
            $docType = $this->restConfig->getDocTypeCreditmemo();

            $invoice = $this->getInvoice($order->getInvoiceId());
            // If a Creditmemo was generated for an invoice, use the created_at value from the invoice
            if ($invoice) {
                $taxCalculationDate = $this->getFormattedDate($store, $invoice->getCreatedAt());
            } else {
                $taxCalculationDate = $this->getFormattedDate($store, $order->getCreatedAt());
            }
        }

        $taxOverride = null;
        if (!is_null($taxCalculationDate)) {
            // Set the tax date for calculation
            $taxOverrideData = [
                'tax_date' => $taxCalculationDate,
                'type' => $this->restConfig->getOverrideTypeDate(),
                'tax_amount' => 0.00,
                'reason' => self::AVATAX_CREDITMEMO_OVERRIDE_REASON,
            ];

            $taxOverride = $this->dataObjectFactory->create(['data' => $taxOverrideData]);

            $validatedData = $this->overrideMetaDataObject->validateData($taxOverride->getData());
            $taxOverride->setData($validatedData);
        }

        $customer = $this->getCustomerById($order->getCustomerId());
        $customerUsageType = $customer ? $this->taxClassHelper->getAvataxTaxCodeForCustomer($customer) : null;

        $orderIncrementId = '';
        /*try {
            $order = $this->orderRepository->get($order->getId());
            $orderIncrementId = $order->getIncrementId();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // Do nothing
        }*/

        $data = [
            'store_id' => $store->getId(),
            'commit' => $this->config->getCommitSubmittedTransactions($store),
            'tax_override' => $taxOverride,
            'customer_code' => $this->customer->getCustomerCodeByCustomerId(
                $order->getCustomerId(),
                rand (90000 , 99999999 ),
                $order->getStoreId()
            ),
            'currency_code' => $order->getOrderCurrencyCode(),
            'entity_use_code' => $customerUsageType,
            'addresses' => [
                $this->restConfig->getAddrTypeTo() => $avaTaxAddress,
            ],
            'code' => rand (90000 , 99999999 ) . '123-' . rand(10000000, 90000000000),
            'type' => $docType,
            'exchange_rate' => $this->getExchangeRate(
                $store,
                $order->getBaseCurrencyCode(),
                $order->getOrderCurrencyCode()
            ),
            'exchange_rate_effective_date' => $currentDate,
            'lines' => $lines,
            'exemption_no' => $certificate_no,
            'reference_code' => $orderIncrementId,
        ];

        $request = $this->dataObjectFactory->create(['data' => $data]);

        $this->addGetTaxRequestFields($request, $store, $address, $order->getCustomerId());

        if ($customer !== null) {
            $this->setIsImporterOfRecord($customer, $request);
        }

        try {
            $validatedData = $this->metaDataObject->validateData($request->getData());
            $request->setData($validatedData);
        } catch (ValidationException $e) {
            $this->avaTaxLogger->error('Error validating data: ' . $e->getMessage(), [
                'data' => var_export($request->getData(), true)
            ]);
        }

        return $request;
    }
}
