<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\Indexer\Conditions;

use Amasty\Orderattr\Model\Attribute\Attribute;
use Amasty\Orderattr\Model\Attribute\Repository;
use Amasty\Orderattr\Model\ResourceModel\Attribute\TableWorker;
use Amasty\Orderattr\Model\Rule\RuleFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Indexer\ActionInterface as IndexerInterface;
use Magento\Framework\Mview\ActionInterface as MviewInterface;
use Magento\Store\Model\StoreManagerInterface;

abstract class AbstractIndexer implements IndexerInterface, MviewInterface
{
    public const MAIN_TABLE = 'amasty_order_attribute_product_index';
    public const REPLICA_TABLE = 'amasty_order_attribute_product_index_replica';

    public const ATTRIBUTE_ID = 'attribute_id';
    public const PRODUCT_ID = 'product_id';

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var Repository
     */
    private $attributeRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var TableWorker
     */
    private $tableWorker;

    /**
     * @var int
     */
    private $batchCount;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        RuleFactory $ruleConditionFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Repository $attributeRepository,
        TableWorker $tableWorker,
        StoreManagerInterface $storeManager,
        int $batchCount = 1000
    ) {
        $this->ruleFactory = $ruleConditionFactory;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->tableWorker = $tableWorker;
        $this->storeManager = $storeManager;
        $this->batchCount = $batchCount;
    }

    abstract protected function getType(): string;

    public function executeFull(): void
    {
        $this->tableWorker->clearReplica(self::REPLICA_TABLE);
        $this->tableWorker->createTemporaryTable(self::REPLICA_TABLE);

        $this->doReindex();

        $this->tableWorker->syncDataFull(self::REPLICA_TABLE);
        $this->tableWorker->switchTables(self::MAIN_TABLE);
    }

    /**
     * @param int[] $ids
     */
    public function executeList(array $ids): void
    {
        $this->executePartial($ids);
    }

    /**
     * @param int $id
     * @return void
     */
    public function executeRow($id): void
    {
        $this->executePartial([$id]);
    }

    /**
     * @param int[] $ids
     */
    public function execute($ids): void
    {
        $this->executePartial($ids);
    }

    /**
     * @param int[]|null $ids
     */
    protected function doReindex(?array $ids = null): void
    {
        $rows = [];
        $count = 0;

        $attributeIds = ($this->getType() === AttributeIndexer::TYPE) ? $ids : null;
        $productIds = ($this->getType() === ProductIndexer::TYPE) ? $ids : null;

        $rule = $this->ruleFactory->create();
        foreach ($this->getAttributes($attributeIds) as $attribute) {
            if ($attribute->getConditionsSerialized()) {
                $rule->clearResult();
                if ($productIds !== null) {
                    $rule->setProductsFilter($productIds);
                }
                $rule->setWebsiteIds($this->getWebsites($attribute->getAvailableInStores()));
                $rule->setConditionsSerialized($attribute->getConditionsSerialized());
                $matchedProducts = $rule->getMatchingProductIds();
                foreach ($matchedProducts as $productId => $data) {
                    $rows[] = [
                        self::PRODUCT_ID => $productId,
                        self::ATTRIBUTE_ID => $attribute->getAttributeId()
                    ];
                    if (++$count >= $this->batchCount) {
                        $this->tableWorker->insert(self::REPLICA_TABLE, $rows);
                        $count = 0;
                        $rows = [];
                    }
                }

            }
        }

        if ($rows) {
            $this->tableWorker->insert(self::REPLICA_TABLE, $rows);
        }
    }

    private function getWebsites(array $storeIds): array
    {
        $websiteIds = [];

        foreach ($storeIds as $storeId) {
            $websiteIds[] = $this->storeManager->getStore($storeId)->getWebsiteId();
        }

        return array_unique($websiteIds);
    }

    /**
     * @param int[]|null $ids
     * @return Attribute[]
     */
    protected function getAttributes(?array $ids = null): array
    {
        $filters = [];

        if ($ids !== null) {
            $filters[] = $this->filterBuilder->setField('attribute_id')
                ->setValue($ids)
                ->setConditionType('in')
                ->create();
        }

        $this->searchCriteriaBuilder->addFilters($filters);

        return $this->attributeRepository->getList($this->searchCriteriaBuilder->create())->getItems();
    }

    /**
     * @param int[] $ids
     */
    public function executePartial(array $ids): void
    {
        $this->tableWorker->createTemporaryTable(self::REPLICA_TABLE);

        $this->doReindex($ids);

        $fieldName = ($this->getType() === AttributeIndexer::TYPE)
            ? self::ATTRIBUTE_ID
            : self::PRODUCT_ID;
        $this->tableWorker->syncDataPartial(
            self::REPLICA_TABLE,
            self::MAIN_TABLE,
            [sprintf('%s IN (?)', $fieldName) => $ids]
        );
    }
}
