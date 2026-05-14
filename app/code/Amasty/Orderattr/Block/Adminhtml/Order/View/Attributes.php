<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Block\Adminhtml\Order\View;

use Amasty\Orderattr\Model\Attribute\RelationValidator;
use Amasty\Orderattr\Model\Entity\EntityData;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order;
use Amasty\Orderattr\Model\Value\Metadata\Form;

class Attributes extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Amasty\Orderattr\Model\Value\Metadata\FormFactory
     */
    private $metadataFormFactory;

    /**
     * @var \Amasty\Orderattr\Model\Entity\EntityResolver
     */
    private $entityResolver;

    /**
     * @var RelationValidator
     */
    private $relationValidator;

    public function __construct(
        Context $context,
        \Amasty\Orderattr\Model\Value\Metadata\FormFactory $metadataFormFactory,
        \Amasty\Orderattr\Model\Entity\EntityResolver $entityResolver,
        array $data = [],
        RelationValidator $relationValidator = null //todo: move to not optional
    ) {
        parent::__construct($context, $data);
        $this->metadataFormFactory = $metadataFormFactory;
        $this->entityResolver = $entityResolver;
        $this->relationValidator = $relationValidator ?? ObjectManager::getInstance()->create(
            RelationValidator::class
        );
    }

    /**
     * Return array of additional account data
     * Value is option style array
     *
     * @return array
     */
    public function getOrderAttributesData()
    {
        $orderAttributesData = [];
        $order = $this->getOrder();
        $entity = $this->entityResolver->getEntityByOrder($order);
        $form = $this->createEntityForm($entity);
        $outputData = $form->outputData(Form::FORMAT_TO_VALIDATE_RELATIONS);

        // array: dependent_attribute_code => bool
        // if true - value should be shown
        $attributesToShow = $this->relationValidator->getAttributesToShow($outputData, $entity);

        foreach ($outputData as $attributeCode => $data) {
            // $attributesToShow contains only dependent attributes.
            //  if there is no attribute in the array - value should be shown.
            if (!array_key_exists($attributeCode, $attributesToShow) || $attributesToShow[$attributeCode]) {
                $attribute = $form->getAttribute($attributeCode);
                if (!empty($data)) {
                    if (is_array($data)) {
                        $data = current($data);
                    }
                    $orderAttributesData[] = [
                        'label' => $attribute->getStoreLabel($order->getStoreId()),
                        'value' => $data
                    ];
                } elseif ($entity->getCustomAttribute($attributeCode)
                    && ($entity->getCustomAttribute($attributeCode)->getValue() === null)
                ) {
                    $orderAttributesData[] = [
                        'label' => $attribute->getStoreLabel($order->getStoreId()),
                        'value' => __('Option was deleted')
                    ];
                }
            }
        }

        return $orderAttributesData;
    }

    /**
     * Return Checkout Form instance
     *
     * @param \Amasty\Orderattr\Model\Entity\EntityData $entity
     * @return Form
     */
    protected function createEntityForm($entity)
    {
        $order = $this->getOrder();
        /** @var Form $formProcessor */
        $formProcessor = $this->metadataFormFactory->create();
        $formProcessor->setFormCode('adminhtml_order_view')
            ->setEntity($entity)
            ->setStore($order->getStore())
            ->setProductIds($this->getProductIds($order))
            ->setShippingMethod((string)$order->getShippingMethod())
            ->setCustomerGroupId((int)$order->getCustomerGroupId());

        return $formProcessor;
    }

    /**
     * @param Order $order
     * @return string[]
     */
    private function getProductIds(Order $order): array
    {
        $productIds = [];
        foreach ($order->getAllItems() as $item) {
            if ($item->getProduct()) {
                $productIds[] = $item->getProduct()->getId();
            }
        }

        return $productIds;
    }

    /**
     * @param string $label
     * @return string
     */
    public function getOrderAttributeEditLink($label = '')
    {
        $link = '';
        if ($this->isAllowedToEdit() && $this->isOrderViewPage()) {
            $label = $label ?: __('Edit');
            $url = $this->getOrderAttributeEditUrl();
            $link = sprintf('<a href="%s">%s</a>', $url, $label);
        }

        return $link;
    }

    /**
     * @return string
     */
    protected function getOrderAttributeEditUrl()
    {
        return $this->getUrl(
            'amorderattr/order_attributes/edit',
            ['order_id' => $this->getOrder()->getId()]
        );
    }

    /**
     * @return bool
     */
    protected function isAllowedToEdit()
    {
        return $this->_authorization->isAllowed('Amasty_Orderattr::attribute_value_edit');
    }

    /**
     * @return Order
     */
    protected function getOrder()
    {
        if (!$this->hasData('order_entity')) {
            $this->setData('order_entity', $this->getParentBlock()->getOrder());
        }
        return $this->getData('order_entity');
    }

    /**
     * @return boolean
     */
    public function isOrderViewPage()
    {
        return (boolean) $this->getOrderInfoArea() == 'order';
    }

    /**
     * @return bool
     */
    public function isShipmentViewPage()
    {
        return (boolean) $this->getOrderInfoArea() == 'shipment';
    }

    /**
     * @return bool
     */
    public function isInvoiceViewPage()
    {
        return (boolean) $this->getOrderInfoArea() == 'invoice';
    }
}
