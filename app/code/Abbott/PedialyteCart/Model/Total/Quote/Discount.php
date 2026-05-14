<?php
namespace Abbott\PedialyteCart\Model\Total\Quote;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;

class Discount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;
    /**
     * Custom constructor.
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->setCode('pdl_discount');
    }

    /**
     * Collect function
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     *
     * @return $this|\Magento\Quote\Model\Quote\Address\Total\AbstractTotal
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $discountAmount = $this->calculateDiscount($quote);
        $quote->setData($this->getCode(), -$discountAmount);
        return $this;
    }

    /**
     * Fetch function
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     *
     * @return array
     */
    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $discountAmount = 0;
        $discountAmount = $this->calculateDiscount($quote);
        return [
            'code'  => 'pdl_discount',
            'title' => $this->getLabel(),
            'value' => $discountAmount  //You can change the reduced amount, or replace it with your own variable
        ];
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return __('Total Savings');
    }

    /**
     * Calculate discount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return float|int
     */
    public function calculateDiscount($quote)
    {
        $totalSavings = 0;
        foreach ($quote->getAllVisibleItems() as $item){
            $qty = $item->getQty();
            $regularPrice = $item->getProduct()->getPrice();
            $finalPrice = $item->getProduct()->getFinalPrice();
            $specialSavings = max(0, $regularPrice - $finalPrice)* $qty;
            $appliedDiscount = $item->getDiscountAmount();;
            $itemSavings = $specialSavings + $appliedDiscount;
            $totalSavings += $itemSavings;
        }
        return $totalSavings;
    }
}
