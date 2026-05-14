<?php
namespace Abbott\Csp\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class CreateCspReportTable implements SchemaPatchInterface
{

    /**
     * Construct function
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(private ModuleDataSetupInterface $moduleDataSetup)
    {
    }

    /**
     * Apply function
     *
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup;
        $setup->getConnection()->startSetup();

        if (!$setup->getConnection()->isTableExists($setup->getTable('abbott_csp_report'))) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable('abbott_csp_report')
            )->addColumn(
                'report_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                'Report ID'
            )->addColumn(
                'document_uri',
                Table::TYPE_TEXT,
                255,
                [],
                'Document URI'
            )->addColumn(
                'violated_directive',
                Table::TYPE_TEXT,
                255,
                [],
                'Violated Directive'
            )->addColumn(
                'blocked_uri',
                Table::TYPE_TEXT,
                255,
                [],
                'Blocked URI'
            )->addColumn(
                'source_file',
                Table::TYPE_TEXT,
                255,
                [],
                'Source File'
            )->addColumn(
                'line_number',
                Table::TYPE_INTEGER,
                null,
                [],
                'Line Number'
            )->addColumn(
                'column_number',
                Table::TYPE_INTEGER,
                null,
                [],
                'Column Number'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['default' => Table::TIMESTAMP_INIT],
                'Created At'
            );

            $setup->getConnection()->createTable($table);
        }

        $setup->getConnection()->endSetup();
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
}
