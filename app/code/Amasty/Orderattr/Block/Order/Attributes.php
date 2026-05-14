<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Block\Order;

use Amasty\Orderattr\Model\Attribute\RelationValidator;
use Amasty\Orderattr\Model\Entity\EntityData;
use Amasty\Orderattr\Model\Entity\EntityResolver;
use Amasty\Orderattr\Model\Value\Metadata\Form;
use Amasty\Orderattr\Model\Value\Metadata\FormFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order;

class Attributes extends Template
{
    /**
     * @var FormFactory
     */
    protected $metadataFormFactory;

    /**
     * @var EntityResolver
     */
    private $entityResolver;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var string[]
     */
    private $escapedInputTypes = ['file', 'html'];

    /**
     * @var RelationValidator
     */
    private $relationValidator;

    public function __construct(
        Template\Context $context,
        FormFactory $metadataFormFactory,
        EntityResolver $entityResolver,
        Registry $coreRegistry,
        array $escapedInputTypes = [],
        array $data = [],
        RelationValidator $relationValidator = null //todo: move to not optional
    ) {
        parent::__construct($context, $data);
        $this->metadataFormFactory = $metadataFormFactory;
        $this->entityResolver = $entityResolver;
        $this->coreRegistry = $coreRegistry;
        $this->escapedInputTypes = array_merge($this->escapedInputTypes, $escapedInputTypes);
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
        $order = $this->getOrder();
        if (!$order) {
            return [];
        }

        $orderAttributesData = [];
        $entity = $this->entityResolver->getEntityByOrder($order);
        $form = $this->createEntityForm($entity);
        $outputData = $form->outputData(Form::FORMAT_TO_VALIDATE_RELATIONS);

        // array: dependent_attribute_code => bool
        // if true - value should be shown
        $attributesToShow = $this->relationValidator->getAttributesToShow($outputData, $entity);

        foreach ($outputData as $attributeCode => $data) {
            if (!empty($data)) {
                // $attributesToShow contains only dependent attributes.
                //  if there is no attribute in the array - value should be shown.
                if (!array_key_exists($attributeCode, $attributesToShow) || $attributesToShow[$attributeCode]) {
                    $attribute = $form->getAttribute($attributeCode);
                    if ($attribute->getIsVisibleOnFront()) {
                        if (is_array($data)) {
                            $data = current($data);
                        }
                        $orderAttributesData[$attributeCode] = [
                            'code' => $attributeCode,
                            'label' => $attribute->getStoreLabel(),
                            'value' => (in_array($attribute->getFrontendInput(), $this->escapedInputTypes, true))
                                ? $data
                                : nl2br($this->escapeHtml($data))
                        ];
                    }
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
        $formProcessor->setFormCode('frontend_order_view')
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
     * @return Order
     */
    protected function getOrder()
    {
        if (!$this->hasData('order_entity')) {
            $order = $this->coreRegistry->registry('current_order');

            if (!$order && $this->getParentBlock()) {
                $order = $this->getParentBlock()->getOrder();
            }

            $this->setData('order_entity', $order);
        }

        return $this->getData('order_entity');
    }
}
