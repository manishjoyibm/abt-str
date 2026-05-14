<?php
namespace Abbott\Fedex\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class FallbackRates extends AbstractFieldArray
{
    private $methodRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     *
     * @throws LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn('shipping_method', [
            'label' => __('Shipping Method'),
            'renderer' =>  $this->getMethodRenderer()
        ]);
        $this->addColumn(
            'subtotal',
            ['label' => __('Subtotal'),'class' => 'required-entry validate-number validate-zero-or-greater']
        );
        $this->addColumn(
            'rate',
            ['label' => __('Rate Price'), 'class' => 'required-entry validate-number validate-zero-or-greater']
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $shippingMethod = $row->getData("shipping_method");
        if ($shippingMethod !== null) {
            $options['option_' . $this->getMethodRenderer()->calcOptionHash($shippingMethod)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Get Method Renderer
     *
     * @return ShippingMethodColumn
     * @throws LocalizedException
     */
    private function getMethodRenderer()
    {
        if (!$this->methodRenderer) {
            $this->methodRenderer = $this->getLayout()->createBlock(
                ShippingMethodColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->methodRenderer;
    }
}
