<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Block\Adminhtml\Attribute\Edit\Tab;

use Amasty\Orderattr\Model\Rule\RuleFactory;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Framework\App\ObjectManager;
use Magento\Rule\Block\ConditionsFactory;
use Magento\Rule\Model\Condition\AbstractCondition;

class Conditions extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Amasty\Orderattr\Model\Attribute\Attribute
     */
    protected $attribute;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    private $shippingConfig;

    /**
     * @var \Amasty\Orderattr\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var Fieldset
     */
    private $rendererFieldset;

    /**
     * @var ConditionsFactory
     */
    private $conditionsFactory;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Amasty\Orderattr\Model\ConfigProvider $configProvider,
        array $data = [],
        ConditionsFactory $conditionsFactory = null, // TODO move to not optional
        Fieldset $rendererFieldset = null, // TODO move to not optional
        RuleFactory $ruleFactory = null // TODO move to not optional
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->shippingConfig = $shippingConfig;
        $this->configProvider = $configProvider;
        $this->conditionsFactory = $conditionsFactory ?? ObjectManager::getInstance()->get(ConditionsFactory::class);
        $this->rendererFieldset = $rendererFieldset ?? ObjectManager::getInstance()->get(Fieldset::class);
        $this->ruleFactory = $ruleFactory ?? ObjectManager::getInstance()->get(RuleFactory::class);
    }

    /**
     * Shipping method options
     *
     * @return array
     */
    protected function getActiveShippingMethods()
    {
        $methods = [];

        $activeCarriers = $this->shippingConfig->getActiveCarriers();

        foreach ($activeCarriers as $carrierCode => $carrierModel) {
            $options = [];
            if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                foreach ($carrierMethods as $methodCode => $method) {
                    if ($carrierCode === 'instore' && $methodCode === 'instore') {
                        $code = \Magento\InventoryInStorePickupShippingApi\Model\Carrier\InStorePickup::DELIVERY_METHOD;
                    } else {
                        $code = $carrierCode . '_' . $methodCode;
                    }
                    $options[] = ['value' => $code, 'label' => $method?:$code];
                }
            }
            $carrierTitle = $this->configProvider->getCarrierTitle($carrierCode);
            $methods[] = ['value' => $options, 'label' => $carrierTitle];
        }

        return $methods;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /**
         * @var \Amasty\Orderattr\Model\Attribute\Attribute $model
         */
        $model = $this->getAttributeObject();
        $formData = [];

        if ($currentShippingMethods = $model->getShippingMethods()) {
            $formData = $currentShippingMethods;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id'     => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                ]
            ]
        );

        $conditionsModel = $this->ruleFactory->create();
        $conditionsModel->setConditions([])
            ->setConditionsSerialized($model->getConditionsSerialized())
            ->getConditions()
            ->setJsFormObject('attribute_conditions');

        $renderer = $this->rendererFieldset->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNameInLayout('amasty_orderattr_conditions')
            ->setNewChildUrl(
                $this->getUrl(
                    'amorderattr/attribute/newConditionHtml',
                    ['form_namespace' => 'attribute_conditions', 'form' => 'attribute_conditions']
                )
            );

        $fieldset = $form->addFieldset(
            'attribute_conditions',
            [
                'legend' => __(
                    'Display the attribute only if the following conditions are met (leave blank to display it always)'
                )
            ]
        )->setRenderer($renderer);

        $fieldset->addField(
            'conditions',
            'text',
            [
                'name'   => 'conditions',
                'label'  => __('Conditions'),
                'title'  => __('Conditions'),
                'data-form-part' => 'attribute_conditions'
            ]
        )->setRule(
            $conditionsModel
        )->setRenderer(
            $this->conditionsFactory->create()
        );

        $fieldset = $form->addFieldset(
            'shipping_fieldset',
            ['legend' => __('Shipping Conditions')]
        );

        $fieldset->addField(
            'shipping_methods',
            'multiselect',
            [
                'name'   => 'shipping_methods[]',
                'label'  => __('Shipping Methods'),
                'title'  => __('Shipping Methods'),
                'note'   => __('Please, note that if shipping methods are NOT selected in the field,
                    order attributes will be displayed on the checkout page right after the page load.
                    And if any is selected, order attributes will appear on the checkout page just when
                    a user selects the shipping method'),
                'values' => $this->getActiveShippingMethods(),
            ]
        );

        $form->addValues(
            [
                'shipping_methods' => $formData
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Return attribute object
     *
     * @return \Amasty\Orderattr\Model\Attribute\Attribute
     */
    public function getAttributeObject()
    {
        if (null === $this->attribute) {
            return $this->_coreRegistry->registry('entity_attribute');
        }

        return $this->attribute;
    }
}
