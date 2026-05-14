<?php
declare(strict_types=1);

namespace Abbott\MetabolicOrdering\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class DeleteExpiredCustomerHistoryRows implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Execute the delete matching your SELECT conditions.
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $table = $this->moduleDataSetup->getTable('customerhistory');

        $connection->startSetup();

        // Deletes rows where comments contain "expired on" and created_at is later than the date found in the comment.
        $sql = <<<SQL
        DELETE ch
        FROM {$table} AS ch
        WHERE ch.comments LIKE '%expired on%'
        AND DATE(ch.created_at) > STR_TO_DATE(
                REGEXP_SUBSTR(ch.comments, '[0-9]{4}-[0-9]{2}-[0-9]{2}'),
                '%Y-%m-%d'
            );
        SQL;

        $connection->query($sql);

        $connection->endSetup();

        return $this;
    }

    /**
     * No dependencies on other patches.
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * No aliases for this patch.
     */
    public function getAliases(): array
    {
        return [];
    }
}
