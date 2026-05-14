<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Plugin\Ui\Mode\Export\MetadataProvider;

use Amasty\Orderattr\Model\Attribute\Repository;
use Exception;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Ui\Model\Export\MetadataProvider;

class ChangeOptionIdToLabel
{
    private const MULTIVALUE_ATTR_TYPES = [
        'multiselect',
        'checkboxes'
    ];

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Repository
     */
    private $attributeRepository;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Repository $attributeRepository
    ) {
        $this->searchCriteriaBuilder =  $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param MetadataProvider $subject
     * @param string[] $result
     * @param DocumentInterface $document
     * @param array $fields
     * @param array $options
     * @return string[]
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function afterGetRowData(
        MetadataProvider $subject,
        array $result,
        DocumentInterface $document,
        array $fields,
        array $options
    ): array {
        try {
            $this->searchCriteriaBuilder->addFilter('attribute_code', $fields, 'in');
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $attributes = $this->attributeRepository->getList($searchCriteria)->getItems();
            foreach ($attributes as $attribute) {
                if (in_array($attribute->getFrontendInput(), self::MULTIVALUE_ATTR_TYPES, true)) {
                    $attributeCode = $attribute->getAttributeCode();
                    $resultId = array_search($attributeCode, $fields);
                    $values = explode(',', (string)$result[$resultId]);
                    if (isset($options[$attributeCode]) && (count($values) > 1)) {
                        foreach ($values as &$value) {
                            $value = $options[$attributeCode][$value];
                        }
                        $result[$resultId] = implode(',', $values);
                    }
                }
            }
        } catch (Exception $e) {
            return $result;
        }

        return $result;
    }
}
