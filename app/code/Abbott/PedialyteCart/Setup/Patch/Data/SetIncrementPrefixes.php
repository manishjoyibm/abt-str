<?php

namespace Abbott\PedialyteCart\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Psr\Log\LoggerInterface;

class SetIncrementPrefixes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var StoreRepositoryInterface
     */
    private StoreRepositoryInterface $storeRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Construct function
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param StoreRepositoryInterface $storeRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        StoreRepositoryInterface $storeRepository,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->storeRepository = $storeRepository;
        $this->logger = $logger;
    }

    /**
     * Update prefix
     *
     */
    public function apply()
    {
        try {
            $this->moduleDataSetup->startSetup();

            $connection = $this->moduleDataSetup->getConnection();
            $tableName = $this->moduleDataSetup->getTable('sales_sequence_profile');

            $pdlStore = $this->storeRepository->get('pedialyte');
            $pdlStoreId = $pdlStore->getId();

            // Set prefix for orders
            $connection->update(
                $tableName,
                ['prefix' => '4'],
                $connection->quoteInto(
                    'meta_id = ?',
                    $this->getMetaId($pdlStoreId, 'order')
                )
            );
            // Set prefix for invoice
            $connection->update(
                $tableName,
                ['prefix' => '4'],
                $connection->quoteInto(
                    'meta_id = ?',
                    $this->getMetaId($pdlStoreId, 'invoice')
                )
            );
            // Set prefix for creditmemo
            $connection->update(
                $tableName,
                ['prefix' => '4'],
                $connection->quoteInto(
                    'meta_id = ?',
                    $this->getMetaId($pdlStoreId, 'creditmemo')
                )
            );
            // Set prefix for shipment
            $connection->update(
                $tableName,
                ['prefix' => '4'],
                $connection->quoteInto(
                    'meta_id = ?',
                    $this->getMetaId($pdlStoreId, 'shipment')
                )
            );
            // Set prefix for rma_item
            $connection->update(
                $tableName,
                ['prefix' => '4'],
                $connection->quoteInto(
                    'meta_id = ?',
                    $this->getMetaId($pdlStoreId, 'rma_item')
                )
            );
            // Set prefix for aw_sarp2_profile
            $connection->update(
                $tableName,
                ['prefix' => '4'],
                $connection->quoteInto(
                    'meta_id = ?',
                    $this->getMetaId($pdlStoreId, 'aw_sarp2_profile')
                )
            );

            $this->moduleDataSetup->endSetup();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Get Dependencies
     *
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get Aliases
     *
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get Meta Id for given entity
     *
     * @param int $storeId
     * @param string $entityType
     * @return int|string
     */
    private function getMetaId($storeId, $entityType)
    {
        try {
            $connection = $this->moduleDataSetup->getConnection();
            $tableName = $this->moduleDataSetup->getTable('sales_sequence_meta');

            $select = $connection->select()
                ->from($tableName, ['meta_id'])
                ->where('store_id = ?', $storeId)
                ->where('entity_type = ?', $entityType);

            return $connection->fetchOne($select);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return 0;
    }
}
