<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Abbott\AdditionalAttributes\DataProvider\Category\Query;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\DB\Select;

/**
 * Provide category attributes for specified category ids and attributes
 */
class CategoryAttributeQuery extends \Magento\CatalogGraphQl\DataProvider\Category\Query\CategoryAttributeQuery
{
    /**
     * @var \Magento\CatalogGraphQl\DataProvider\AttributeQueryFactory
     */
    private $attributeQueryFactory;

    /**
     * @var array
     */
    private static $requiredAttributes = [
        'entity_id',
    ];

    /**
     * @param \Magento\CatalogGraphQl\DataProvider\AttributeQueryFactory $attributeQueryFactory
     */
    public function __construct(
        \Magento\CatalogGraphQl\DataProvider\AttributeQueryFactory $attributeQueryFactory
    ) {
        $this->attributeQueryFactory = $attributeQueryFactory;
    }

    /**
     * Form and return query to get eav attributes for given categories
     *
     * @param array $categoryIds
     * @param array $categoryAttributes
     * @param int $storeId
     * @return Select
     * @throws \Zend_Db_Select_Exception
     */
    public function getQuery(array $categoryIds, array $categoryAttributes, int $storeId): Select
    {
        /**
         * Add include_in_menu in query
         */
        array_push($categoryAttributes, 'include_in_menu');

        $categoryAttributes = \array_merge($categoryAttributes, self::$requiredAttributes);

        $attributeQuery = $this->attributeQueryFactory->create(
            [
                'entityType' => CategoryInterface::class
            ]
        );

        return $attributeQuery->getQuery($categoryIds, $categoryAttributes, $storeId);
    }
}
