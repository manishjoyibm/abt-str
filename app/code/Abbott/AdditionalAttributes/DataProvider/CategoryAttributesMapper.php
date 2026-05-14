<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Abbott\AdditionalAttributes\DataProvider;

use Magento\Framework\GraphQl\ConfigInterface;
use Magento\Framework\GraphQl\Config\Element\Type;
use Magento\Framework\GraphQl\Config\Element\InterfaceType;

/**
 * Map for category attributes.
 */
class CategoryAttributesMapper extends \Magento\CatalogGraphQl\DataProvider\CategoryAttributesMapper
{
    /**
     * @var ConfigInterface
     */
    private $graphqlConfig;

    /**
     * @param ConfigInterface $graphqlConfig
     */
    public function __construct(
        ConfigInterface $graphqlConfig
    ) {
        $this->graphqlConfig = $graphqlConfig;
    }

    /**
     * Returns attribute values for given attribute codes.
     *
     * @param array $fetchResult
     * @return array
     */
    public function getAttributesValues(array $fetchResult): array
    {
        $attributes = [];

        foreach ($fetchResult as $row) {
            if (!isset($attributes[$row['entity_id']])) {
                $attributes[$row['entity_id']] = $row;
                //TODO: do we need to introduce field mapping?
                $attributes[$row['entity_id']]['id'] = $row['entity_id'];
            }

            if (isset($row['attribute_code'])) {
                $attributes[$row['entity_id']][$row['attribute_code']] = $row['value'];
            }
        }

        /**
         *  Remove include_in_menu attribute category whos value is o in layered navigation
         */
        foreach ($attributes as $key => $attribute) {
            if ((array_key_exists('include_in_menu', $attribute)) && ($attribute['include_in_menu'] == 0)) {
                unset($attributes[$key]);
            }
        }

        return $this->formatAttributes($attributes);
    }

    /**
     * Format attributes that should be converted to array type
     *
     * @param array $attributes
     * @return array
     */
    private function formatAttributes(array $attributes): array
    {
        $arrayTypeAttributes = $this->getFieldsOfArrayType();

        return $arrayTypeAttributes
            ? array_map(
                function ($data) use ($arrayTypeAttributes) {
                    foreach ($arrayTypeAttributes as $attributeCode) {
                        $data[$attributeCode] = $this->valueToArray($data[$attributeCode] ?? null);
                    }
                    return $data;
                },
                $attributes
            )
            : $attributes;
    }

    /**
     * Get fields that should be converted to array type
     *
     * @return array
     */
    private function getFieldsOfArrayType(): array
    {
        $categoryTreeSchema = $this->graphqlConfig->getConfigElement('CategoryTree');
        if (!$categoryTreeSchema instanceof Type) {
            throw new \LogicException('CategoryTree type not defined in schema.');
        }

        $fields = [];
        foreach ($categoryTreeSchema->getInterfaces() as $interface) {
            /** @var InterfaceType $configElement */
            $configElement = $this->graphqlConfig->getConfigElement($interface['interface']);

            foreach ($configElement->getFields() as $field) {
                if ($field->isList()) {
                    $fields[] = $field->getName();
                }
            }
        }

        return $fields;
    }

    /**
     * Cast string to array
     *
     * @param string|null $value
     * @return array
     */
    private function valueToArray($value): array
    {
        return $value ? \explode(',', $value) : [];
    }
}
