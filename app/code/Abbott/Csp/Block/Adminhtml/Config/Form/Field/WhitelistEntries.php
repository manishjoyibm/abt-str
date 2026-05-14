<?php
namespace Abbott\Csp\Block\Adminhtml\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class WhitelistEntries extends AbstractFieldArray
{
    /**
     * @var $typeRenderer
     */
    protected $typeRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     *
     * @throws LocalizedException
     */
    protected function _prepareToRender(): void
    {
        $this->addColumn(
            'url',
            ['label' => __('URL/HASH')]
        );

        $this->addColumn(
            'type',
            ['label' => __('Type'),
              'renderer' =>  $this->getTypeRenderer()
            ]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Record');
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
        $type = $row->getData("type");
        if ($type !== null) {
            $options['option_' . $this->getTypeRenderer()->calcOptionHash($type)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Get Type Renderer
     *
     * @return Type
     * @throws LocalizedException
     */
    private function getTypeRenderer(): Type
    {
        if (!$this->typeRenderer) {
            $this->typeRenderer = $this->getLayout()->createBlock(
                Type::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->typeRenderer;
    }
}
