<?php


namespace Abbott\Quote\Plugin\Model;

use Magento\Framework\Api\ExtensibleDataObjectConverter;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote as QuoteEntity;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteManagement;

class QuoteManagementPlugin
{
    /**
     * @var ExtensibleDataObjectConverter
     */
    private ExtensibleDataObjectConverter $dataObjectConverter;

    /**
     * QuoteManagementPlugin constructor.
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     */
    public function __construct(ExtensibleDataObjectConverter $dataObjectConverter)
    {
        $this->dataObjectConverter = $dataObjectConverter;
    }

    /**
     * Before submit method
     *
     * @param QuoteManagement $subject
     * @param QuoteEntity $quote
     * @param array $orderData
     * @return array
     */
    public function beforeSubmit(QuoteManagement $subject, QuoteEntity $quote, array $orderData = []): array
    {
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();
        $billing = $quote->getBillingAddress();
        if ($shipping && $billing->getSaveInAddressBook() == $shipping->getSaveInAddressBook()) {
            $shipping->setSameAsBilling($this->isAddressesAreEqual($quote));
            $quote->setShippingAddress($shipping);
        }

        $items = $quote->getAllItems();
        $itemsById = [];
        foreach ($items as $item) {
            if (!isset($itemsById[$item->getProduct()->getId()])) {
                $itemsById[$item->getProduct()->getId()] = [];
            }
            $itemsById[$item->getProduct()->getId()][] = $item;
        }

        foreach ($itemsById as $itemById) {
            if (count($itemById) > 1) {
                $mainItem = null;
                /** @var Item $item */
                foreach ($itemById as $item) {
                    if (!$mainItem) {
                        $mainItem = $item;
                        continue;
                    }

                    if ($this->compareBuyRequests($item, $mainItem)) {
                        $mainItem->setQty($mainItem->getQty() + $item->getQty());
                        $quote->deleteItem($item);
                        $mainItem->calcRowTotal();
                    }
                }
            }
        }
        return [$quote, $orderData];
    }

    /**
     * Checks if shipping and billing addresses are equal.
     *
     * @param QuoteEntity $quote
     * @return bool
     */
    private function isAddressesAreEqual(QuoteEntity $quote): bool
    {
        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();
        $shippingData = $this->dataObjectConverter->toFlatArray($shippingAddress, [], AddressInterface::class);
        $billingData = $this->dataObjectConverter->toFlatArray($billingAddress, [], AddressInterface::class);
        unset(
            $shippingData['id'],
            $shippingData['same_as_billing'],
            $shippingData['save_in_address_book'],
            $shippingData['address_type'],
            $shippingData['entity_id'],
            $billingData['id'],
            $billingData['same_as_billing'],
            $billingData['save_in_address_book'],
            $billingData['address_type'],
            $billingData['entity_id']
        );

        $shippingData = array_filter($shippingData, function ($e) {
            return !($e == "" || $e == null);
        });

        $billingData = array_filter($billingData, function ($e) {
            return !($e == "" || $e == null);
        });

        return $shippingData == $billingData;
    }

    /**
     * Compare Buy Requests
     *
     * @param Item $item
     * @param Item $mainItem
     * @return bool
     */
    protected function compareBuyRequests(
        Item $item,
        Item $mainItem
    ) : bool {
        $buyRequest = $item->getBuyRequest()->getData();
        unset($buyRequest["qty"], $buyRequest["original_qty"]);

        $mainBuyRequest = $mainItem->getBuyRequest()->getData();
        unset($mainBuyRequest["qty"], $mainBuyRequest["original_qty"]);

        return $mainBuyRequest == $buyRequest;
    }
}
