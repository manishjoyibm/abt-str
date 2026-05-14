<?php
declare(strict_types=1);

namespace Abbott\MetabolicOrdering\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddEmailFlags implements SchemaPatchInterface
{
    /** @var SchemaSetupInterface */
    private $schemaSetup;

    public function __construct(SchemaSetupInterface $schemaSetup)
    {
        $this->schemaSetup = $schemaSetup;
    }

    public function apply()
    {
        $setup = $this->schemaSetup;
        $setup->startSetup();

        $conn = $setup->getConnection();
        $table = $setup->getTable('metabolic_ordering');

        // Add columns if not exists (idempotent)
        if (!$conn->tableColumnExists($table, 'expiry_email_sent')) {
            $conn->addColumn($table, 'expiry_email_sent', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Expiry Email Sent Flag (0/1)'
            ]);
        }
        if (!$conn->tableColumnExists($table, 'expiry_email_sent_at')) {
            $conn->addColumn($table, 'expiry_email_sent_at', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                'nullable' => true,
                'comment' => 'Expiry Email Sent At'
            ]);
        }
        if (!$conn->tableColumnExists($table, 'threshold_email_sent')) {
            $conn->addColumn($table, 'threshold_email_sent', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Threshold Email Sent Flag (0/1)'
            ]);
        }
        if (!$conn->tableColumnExists($table, 'threshold_email_sent_at')) {
            $conn->addColumn($table, 'threshold_email_sent_at', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                'nullable' => true,
                'comment' => 'Threshold Email Sent At'
            ]);
        }

        $setup->endSetup();
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}