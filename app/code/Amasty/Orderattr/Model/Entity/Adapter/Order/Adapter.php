<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Entity\Adapter\Order;

use Amasty\Orderattr\Api\Data\AttributeValueInterface;
use Amasty\Orderattr\Model\Entity\EntityData;
use Amasty\Orderattr\Model\Entity\EntityData\Converter\ConvertAttributeValue;
use Amasty\Orderattr\Model\Entity\EntityDataRepository;
use Amasty\Orderattr\Model\Entity\EntityResolver;
use Amasty\Orderattr\Model\Entity\Handler\Save;
use Amasty\Orderattr\Model\Value\Metadata\Form;
use Amasty\Orderattr\Model\Value\Metadata\FormFactory;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Adapter
{
    /**
     * @var OrderExtensionFactory
     */
    private $orderExtensionFactory;

    /**
     * @var EntityResolver
     */
    private $entityResolver;

    /**
     * @var Save
     */
    private $saveHandler;

    /**
     * @var FormFactory
     */
    private $metadataFormFactory;

    /**
     * @var ConvertAttributeValue
     */
    private $convertAttributeValue;

    /**
     * @var EntityDataRepository
     */
    private $entityDataRepository;

    public function __construct(
        OrderExtensionFactory $orderExtensionFactory,
        EntityResolver $entityResolver,
        Save $saveHandler,
        FormFactory $metadataFormFactory,
        EntityDataRepository $entityDataRepository,
        ConvertAttributeValue $convertAttributeValue
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->entityResolver = $entityResolver;
        $this->saveHandler = $saveHandler;
        $this->metadataFormFactory = $metadataFormFactory;
        $this->convertAttributeValue = $convertAttributeValue;
        $this->entityDataRepository = $entityDataRepository;
    }

    /**
     * @param OrderInterface $order
     * @param bool $force
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @return void
     */
    public function addExtensionAttributesToOrder(OrderInterface $order, bool $force = false): void
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if (empty($extensionAttributes)) {
            $extensionAttributes = $this->orderExtensionFactory->create();
            $order->setExtensionAttributes($extensionAttributes);
        }
        if (!$force && !empty($extensionAttributes->getAmastyOrderAttributes())) {
            return;
        }

        $entity = $this->entityResolver->getEntityByOrder($order);
        $customAttributes = $entity->getCustomAttributes();

        if (!empty($customAttributes)) {
            $customAttributes = $this->replaceAttributeValues($customAttributes);
            $extensionAttributes->setAmastyOrderAttributes($customAttributes);
        }
        $order->setExtensionAttributes($extensionAttributes);
        $this->setOrderData($order, $entity, $extensionAttributes->getAmastyOrderAttributes());
    }

    /**
     * @param OrderInterface $order
     * @throws LocalizedException
     * @throws CouldNotSaveException
     * @return void
     */
    public function saveOrderValues(OrderInterface $order): void
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getAmastyOrderAttributes()) {
            $entity = $this->entityResolver->getEntityByOrder($order);
            $attributes = $extensionAttributes->getAmastyOrderAttributes();
            $entityType = $entity->getParentEntityType();
            $parentId = $entity->getParentId();
            $entityId = $entity->getEntityId();
            $entity->unsetData();
            $entity->setParentEntityType($entityType);
            $entity->setParentId($parentId);
            $entity->setEntityId($entityId);
            $entity->setCustomAttributes($attributes);
            $this->setOrderData($order, $entity, $attributes);
            $this->saveHandler->execute($entity);
        }
    }

    public function deleteAttributeEntityByOrder(OrderInterface $order): void
    {
        /** @var EntityData $orderattrEntity */
        $orderAttrEntity = $this->entityResolver->getEntityByOrder($order);
        $this->entityDataRepository->delete($orderAttrEntity);
    }

    /**
     * @param OrderInterface $order
     * @param EntityData $entity
     * @param AttributeValue[] $attributes
     */
    private function setOrderData(
        OrderInterface $order,
        EntityData $entity,
        $attributes
    ) {
        if (!is_array($attributes)) {
            return;
        }
        $form = $this->createEntityForm($entity);
        $data = $form->outputData();

        foreach ($attributes as $orderAttribute) {
            $attributeCode = $orderAttribute->getAttributeCode();
            if (!empty($data[$attributeCode])) {
                $order->setData($attributeCode, $data[$attributeCode]);
            }
        }
    }

    /**
     * Return Form instance
     *
     * @param EntityData $entity
     * @return Form
     */
    protected function createEntityForm($entity)
    {
        /** @var Form $formProcessor */
        $formProcessor = $this->metadataFormFactory->create();
        $formProcessor->setFormCode('all_attributes')
            ->setEntity($entity)
            ->setInvisibleIgnored(false);

        return $formProcessor;
    }

    /**
     * @param AttributeInterface[] $attributeValues
     * @return AttributeValueInterface[]
     */
    private function replaceAttributeValues(array $attributeValues): array
    {
        $result = [];
        foreach ($attributeValues as $attributeValue) {
            $result[] = $this->convertAttributeValue->execute($attributeValue);
        }

        return $result;
    }
}
