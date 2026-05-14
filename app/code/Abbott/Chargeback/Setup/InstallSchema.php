<?php

namespace Abbott\Chargeback\Setup;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var Magento\Framework\Filesystem\Io\File
     */
    protected $_io;

    /**
     * @var Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;

    /**
     * @param Magento\Framework\Filesystem\Io\File  $io
     * @param Magento\Framework\App\Filesystem\DirectoryList  $directoryList
     */
    public function __construct(
        File $io,
        DirectoryList $directoryList
    ) {
        $this->_io = $io;
        $this->_directoryList = $directoryList;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        /**
         * Create a directory
         *
         * @param string "directory path"
         * @param int "directory permission"
         * @return bool
         */
        $this->_io->mkdir($this->_directoryList->getPath('var') . '/Abbott/Chargeback', 0755);
        $this->_io->mkdir($this->_directoryList->getPath('var') . '/Abbott/Chargeback/PDE-0017', 0755);
        $this->_io->mkdir($this->_directoryList->getPath('var') . '/Abbott/Chargeback/Archive', 0755);
        $installer->endSetup();
    }
}
