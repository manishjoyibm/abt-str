<?php
declare(strict_types=1);
namespace Abbott\AdditionalAttributes\Plugin;

use Magento\CatalogGraphQl\DataProvider\Category\Query\CategoryAttributeQuery;
use Magento\CatalogGraphQl\DataProvider\CategoryAttributesMapper;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\RootCategoryProvider;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\AggregationValueInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class Category
{

    /**
     * @var string
     */
    const CATEGORY_BUCKET = 'category_bucket';

    /**
     * @var CategoryAttributeQuery
     */
    private $categoryAttributeQuery;

    /**
     * @var CategoryAttributesMapper
     */
    private $attributesMapper;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var RootCategoryProvider
     */
    private $rootCategoryProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionFactory
     */
    private $categoryCollection;


    /**
     * Category constructor.
     * @param CategoryAttributeQuery $categoryAttributeQuery
     * @param CategoryAttributesMapper $attributesMapper
     * @param RootCategoryProvider $rootCategoryProvider
     * @param ResourceConnection $resourceConnection
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $categoryCollection
     */
    public function __construct(
        CategoryAttributeQuery $categoryAttributeQuery,
        CategoryAttributesMapper $attributesMapper,
        RootCategoryProvider $rootCategoryProvider,
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager,
        CollectionFactory $categoryCollection
    ) {
        $this->categoryAttributeQuery = $categoryAttributeQuery;
        $this->attributesMapper = $attributesMapper;
        $this->resourceConnection = $resourceConnection;
        $this->rootCategoryProvider = $rootCategoryProvider;
        $this->storeManager = $storeManager;
        $this->categoryCollection = $categoryCollection;
    }

    /**
     * @param \Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Category $subject
     * @param $result
     * @param AggregationInterface $aggregation
     * @param $storeId
     * @throws \Zend_Db_Select_Exception
     */
    public function afterBuild(
        \Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Category $subject,
        $result,
        AggregationInterface $aggregation,
        $storeId
        )
        {
        $bucket = $aggregation->getBucket(self::CATEGORY_BUCKET);
        $categoryIds = \array_map(
            function (AggregationValueInterface $value) {
                return (int)$value->getValue();
            },
            $bucket->getValues()
        );
        $categoryIds = \array_diff($categoryIds, [$this->rootCategoryProvider->getRootCategory($storeId)]);

        $finalCategoryIds = $this->filterCategoryStoreWise($categoryIds, $storeId);

        $categoryLabels = \array_column(
            $this->attributesMapper->getAttributesValues(
                $this->resourceConnection->getConnection()->fetchAll(
                    $this->categoryAttributeQuery->getQuery($finalCategoryIds, ['name'], $storeId)
                )
            ),
            'name',
            'entity_id'
        );

        return $this->getResult($result, $categoryLabels);
    }

    /**
     * @param $result
     * @param $categoryLabels
     * @return mixed
     */
    public function getResult($result, $categoryLabels)
    {
        foreach ($result as $key => $value) {
            foreach ($value['options'] as $val => $option) {
                if (!in_array($option['label'], $categoryLabels)) {
                    unset($result[$key]['options'][$val]);
                }
            }
        }

        return $result;
    }

    /**
     * @param $category_ids
     * @param $storeId
     * @return array
     */
    private function filterCategoryStoreWise($categoryId, $storeId)
    {
        $categoryIds = [];

        $rootId = $this->storeManager->getStore($storeId)->getRootCategoryId();

        $categories = $this->categoryCollection->create()
            ->addAttributeToSelect('entity_id')
            ->setStore($this->storeManager->getStore())
            ->addFieldToFilter('path', array('like'=> "$storeId/$rootId/%"))
            ->addFieldToFilter('entity_id', ['in' => $categoryId]);

        foreach ($categories as $category) {
            array_push($categoryIds, $category->getId());
        }

        return $categoryIds;
    }
}
