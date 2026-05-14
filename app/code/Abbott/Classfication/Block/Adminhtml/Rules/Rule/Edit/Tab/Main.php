<?php

namespace Abbott\Classfication\Block\Adminhtml\Rules\Rule\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreRepository;

class Main extends Generic implements TabInterface
{

    public $groups;
    public $storeRepository;
    public $attributeCollection;
    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Collection $groups
     * @param StoreRepository $storeRepository
     * @param \Amasty\Orderattr\Model\ResourceModel\Attribute\Collection $attributeCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Collection $groups,
        StoreRepository $storeRepository,
        \Amasty\Orderattr\Model\ResourceModel\Attribute\Collection $attributeCollection,
        array $data = []
    ) {
        $this->groups = $groups;
        $this->storeRepository = $storeRepository;
        $this->attributeCollection = $attributeCollection;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Rule Information');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        $this->getTabLabel();
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Generic
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_rule');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);

        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id']);
        }

        $fieldset->addField(
            'name',
            'text',
            ['name' => 'name', 'label' => __('Rule Name'), 'title' => __('Rule Name'), 'required' => true]
        );

        $fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'style' => 'height: 100px;'
            ]
        );

        $fieldset->addField(
            'rule_website_ids',
            'multiselect',
            [
                'label' => __('Websites'),
                'title' => __('Websites'),
                'name' => 'rule_website_ids',
                'required' => true,
                'values' => $this->getStoreOptionArray()
            ]
        );

        $fieldset->addField(
            'rule_customer_group',
            'multiselect',
            [
                'label' => __('Customer Group'),
                'title' => __('Customer Group'),
                'name' => 'rule_customer_group',
                'required' => true,
                'values' => $this->groups->toOptionArray()
            ]
        );

        $fieldset->addField(
            'rule_order_attribute',
            'select',
            [
                'label' => __('Order Attribute'),
                'title' => __('Order Attribute'),
                'name' => 'rule_order_attribute',
                'required' => false,
                'values' =>  $this->orderAttributesArray()
            ]
        );

        $fieldset->addField(
            'order_attr_type',
            'select',
            [
             'label' => __('Order Attribute Value'),
             'title' => __('Order Attribute Value'),
             'name' => 'order_attr_type',
             'required' => true,
             'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        $fieldset->addField(
            'order_classification',
            'text',
            [
                'label' => __('Order Classfication'),
                'title' => __('Order Classfication'),
                'name' => 'order_classification',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_active',
                'required' => true,
                'options' => ['1' => __('Active'), '0' => __('Inactive')]
            ]
        );

        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }

        $fieldset->addField('sort_order', 'text', ['name' => 'sort_order', 'label' => __('Priority')]);

        $form->setValues($model->getData());

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $this->setForm($form);

        $this->_eventManager->dispatch('adminhtml_rules_rule_edit_tab_main_prepare_form', ['form' => $form]);

        return parent::_prepareForm();
    }

    /**
     * Get Store Option Array
     *
     * @return array
     */
    public function getStoreOptionArray()
    {
        $stores = $this->storeRepository->getList();
        $storeList = [];
        foreach ($stores as $store) {
            $storeList[] = ['label' => $store['name'], 'value' => $store['store_id']];
        }
        return $storeList;
    }

    /**
     * Get Order Attributes Array
     *
     * @return array
     */
    public function orderAttributesArray()
    {
        $attrsArray[] = ['label' => '-Please Select-', 'value' => ''];
        foreach ($this->attributeCollection as $attr) {
            $attrsArray[] = ['label' => $attr['attribute_code'], 'value' => $attr['attribute_code']];
        }

        return $attrsArray;
    }
}
