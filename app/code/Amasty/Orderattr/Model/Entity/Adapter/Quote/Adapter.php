<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Entity\Adapter\Quote;

use Amasty\Orderattr\Api\Data\AttributeValueInterface;
use Amasty\Orderattr\Model\Attribute\ForbidValidator;
use Amasty\Orderattr\Model\Entity\EntityData\Converter\ConvertAttributeValue;
use Amasty\Orderattr\Model\Entity\EntityResolver;
use Amasty\Orderattr\Model\Entity\Handler\Save;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartInterface;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Adapter
{
    /**
     * @var CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @var EntityResolver
     */
    private $entityResolver;

    /**
     * @var Save
     */
    private $saveHandler;

    /**
     * @var ForbidValidator
     */
    private $forbidValidator;

    /**
     * @var ConvertAttributeValue
     */
    private $convertAttributeValue;

    public function __construct(
        CartExtensionFactory $cartExtensionFactory,
        EntityResolver $entityResolver,
        Save $saveHandler,
        ForbidValidator $forbidValidator,
        ConvertAttributeValue $convertAttributeValue
    ) {
        $this->cartExtensionFactory = $cartExtensionFactory;
        $this->entityResolver = $entityResolver;
        $this->saveHandler = $saveHandler;
        $this->forbidValidator = $forbidValidator;
        $this->convertAttributeValue = $convertAttributeValue;
    }

    /**
     * @param CartInterface $quote
     * @param bool $force
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @return void
     */
    public function addExtensionAttributesToQuote(CartInterface $quote, bool $force = false): void
    {
        $extensionAttributes = $quote->getExtensionAttributes();
        if (empty($extensionAttributes)) {
            $extensionAttributes = $this->cartExtensionFactory->create();
            $quote->setExtensionAttributes($extensionAttributes);
        }
        if (!$force && !empty($extensionAttributes->getAmastyOrderAttributes())) {
            return;
        }

        $entity = $this->entityResolver->getEntityByQuoteId($quote->getId());
        $customAttributes = $entity->getCustomAttributes();

        if (!empty($customAttributes)) {
            $customAttributes = $this->replaceAttributeValues($customAttributes);
            $extensionAttributes->setAmastyOrderAttributes($customAttributes);
        }
        $quote->setExtensionAttributes($extensionAttributes);
    }

    /**
     * @param CartInterface $quote
     * @throws CouldNotSaveException
     * @return void
     */
    public function saveQuoteValues(CartInterface $quote): void
    {
        $extensionAttributes = $quote->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getAmastyOrderAttributes()) {
            $entity = $this->entityResolver->getEntityByQuoteId($quote->getId());
            $attributes = $extensionAttributes->getAmastyOrderAttributes();

            foreach ((array)$attributes as $key => $attribute) {
                if ($this->forbidValidator->shouldDeleteAttributeValue($quote, $attribute->getAttributeCode())) {
                    if (!empty($entity->getForbiddenAttributeCodes())) {
                        $forbidAttributeCodes = $entity->getForbiddenAttributeCodes();
                    }
                    $forbidAttributeCodes[] = $attribute->getAttributeCode();
                    $entity->setForbiddenAttributeCodes($forbidAttributeCodes);
                    unset($attributes[$key]);
                }
            }

            $entity->setCustomAttributes($attributes);
            $this->saveHandler->execute($entity);
        }
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
