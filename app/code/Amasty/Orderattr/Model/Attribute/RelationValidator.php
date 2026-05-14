<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Attribute;

use Amasty\Orderattr\Model\Entity\EntityData;
use Amasty\Orderattr\Model\ResourceModel\Attribute\Relation\RelationDetails\Collection;
use Amasty\Orderattr\Model\ResourceModel\Attribute\Relation\RelationDetails\CollectionFactory;

class RelationValidator
{
    /**
     * @var CollectionFactory
     */
    private $attributeRelationCollectionFactory;

    public function __construct(
        CollectionFactory $attributeRelationCollectionFactory
    ) {
        $this->attributeRelationCollectionFactory = $attributeRelationCollectionFactory;
    }

    public function validateRelations(array $data): array
    {
        $collection = $this->attributeRelationCollectionFactory->create();
        $collection->joinDependAttributeCode();

        $attributesToSave = [];
        /** @var \Amasty\Orderattr\Model\Attribute\Relation\RelationDetails $relation */
        foreach ($collection->getItems() as $relation) {
            if (!array_key_exists($relation->getData('parent_attribute_code'), $data)
                || (
                    isset($attributesToSave[$relation->getData('parent_attribute_code')])
                    && !$attributesToSave[$relation->getData('parent_attribute_code')]
                )
            ) {
                $attributesToSave[$relation->getData('dependent_attribute_code')] = false;
                $attributesToSave = $this->validateNestedRelations($attributesToSave, $collection);
                //unset nested
            } else {
                foreach ($data as $attributeCode => $attributeValue) {
                    // is attribute have relations
                    if ($relation->getData('parent_attribute_code') === $attributeCode) {
                        $code = $relation->getData('dependent_attribute_code');
                        if (is_array($attributeValue) && (count($attributeValue) === 1)) {
                            $attributeValue = explode(',', current($attributeValue));
                        }
                        /**
                         * Is not to show - hide;
                         * false - value should to be saved and validated
                         */
                        $attributesToSave[$code] = (bool)
                            (isset($attributesToSave[$code]) && $attributesToSave[$code])
                            || ($relation->getOptionId() == $attributeValue)
                            || (is_array($attributeValue) && in_array($relation->getOptionId(), $attributeValue));
                    }
                }
            }
        }

        return $this->validateNestedRelations($attributesToSave, $collection);
    }

    /**
     * Check relation chain.
     * Example: we have
     *      relation1 - attribute1 = someAttribute1, dependAttribute1 = hidedSelect1
     *      relation2 - attribute2 = hidedSelect1, dependAttribute2 = someAttribute2
     *  where relation1.dependAttribute1 == relation2.attribute2
     *
     * @param array $isValidArray
     * @param Collection $relations
     *
     * @return array
     */
    private function validateNestedRelations(array $isValidArray, Collection $relations): array
    {
        $isNestedFind = false;
        foreach ($relations->getItems() as $relation) {
            $parentCode = $relation->getData('parent_attribute_code');
            $dependCode = $relation->getData('dependent_attribute_code');
            if (array_key_exists($parentCode, $isValidArray) && !$isValidArray[$parentCode]
                && (!array_key_exists($dependCode, $isValidArray) || $isValidArray[$dependCode])
            ) {
                $isValidArray[$dependCode] = false;
                $isNestedFind = true;
            }
        }
        if ($isNestedFind) {
            $isValidArray = $this->validateNestedRelations($isValidArray, $relations);
        }

        return $isValidArray;
    }

    public function getAttributesToShow(array $outputData, EntityData $entity): array
    {
        $dataToValidate = [];
        foreach ($outputData as $key => $value) {
            $attributeData = $entity->getCustomAttribute($key);
            if ($attributeData) {
                // for multiselect and checkbox we need array to validate relations
                $dataToValidate[$key] = is_array($value)
                    ? [$entity->getCustomAttribute($key)->getValue()]
                    : $entity->getCustomAttribute($key)->getValue();
            } else {
                $dataToValidate[$key] = '';
            }
        }

        return $this->validateRelations($dataToValidate);
    }
}
