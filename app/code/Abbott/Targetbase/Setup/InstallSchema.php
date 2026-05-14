<?php

namespace Abbott\Targetbase\Setup;

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
    protected $io;

    /**
     * @var Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @param Magento\Framework\Filesystem\Io\File  $io
     * @param Magento\Framework\App\Filesystem\DirectoryList  $directoryList
     */
    public function __construct(
        File $io,
        DirectoryList $directoryList
    ) {
        $this->io = $io;
        $this->directoryList = $directoryList;
    }

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
        $this->io->mkdir($this->directoryList->getPath('var') . '/Targetbase', 0755);
        $this->io->mkdir($this->directoryList->getPath('var') . '/Targetbase/Orders', 0755);
        $this->io->mkdir($this->directoryList->getPath('var') . '/Targetbase/Orders/Archive', 0755);
        $this->io->mkdir($this->directoryList->getPath('var') . '/Targetbase/Customers', 0755);
        $this->io->mkdir($this->directoryList->getPath('var') . '/Targetbase/Customers/Archive', 0755);
        $installer->endSetup();
    }
}
