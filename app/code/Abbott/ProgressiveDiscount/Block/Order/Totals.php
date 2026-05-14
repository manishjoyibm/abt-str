<?php

namespace Abbott\ProgressiveDiscount\Block\Order;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

/**
 * @api
 * @since 100.0.2
 */
class Totals extends \Magento\Sales\Block\Order\Totals
{
    public const LABEL = 'label';
    public const FVALUE = 'value';
    public const CFIELD = 'field';
    public const GRANDTOTAL = 'grand_total';

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $registry, $data);
    }

    /**
     * Initialize order totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        $source = $this->getSource();
        $this->_totals = [];
        $this->_totals['subtotal'] = new \Magento\Framework\DataObject(
            ['code' => 'subtotal', self::FVALUE => $source->getSubtotal(), self::LABEL => __('Subtotal')]
        );
        /**
         * Add shipping
         */
        if (!$source->getIsVirtual() && ((double)$source->getShippingAmount() || $source->getShippingDescription())) {
            $this->_totals['shipping'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'shipping',
                    self::CFIELD => 'shipping_amount',
                    self::FVALUE => $this->getSource()->getShippingAmount(),
                    self::LABEL => __('Shipping & Handling'),
                ]
            );
        }
        /**
         * Add discount
         */
        if ((double)$this->getSource()->getDiscountAmount() != 0) {
            if ($this->getSource()->getDiscountDescription()) {
                $discountLabel = __($source->getDiscountDescription());
            } else {
                $discountLabel = __('Discount');
            }
            $this->_totals['discount'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'discount',
                    self::CFIELD => 'discount_amount',
                    self::FVALUE => $source->getDiscountAmount(),
                    self::LABEL => $discountLabel,
                ]
            );
        }
        $this->_totals[self::GRANDTOTAL] = new \Magento\Framework\DataObject(
            [
                'code' => self::GRANDTOTAL,
                self::CFIELD => self::GRANDTOTAL,
                'strong' => true,
                self::FVALUE => $source->getGrandTotal(),
                self::LABEL => __('Grand Total'),
            ]
        );
        /**
         * Base grandtotal
         */
        if ($this->getOrder()->isCurrencyDifferent()) {
            $this->_totals['base_grandtotal'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'base_grandtotal',
                    self::FVALUE => $this->getOrder()->formatBasePrice($source->getBaseGrandTotal()),
                    self::LABEL => __('Grand Total to be Charged'),
                    'is_formated' => true,
                ]
            );
        }
        return $this;
    }
}
