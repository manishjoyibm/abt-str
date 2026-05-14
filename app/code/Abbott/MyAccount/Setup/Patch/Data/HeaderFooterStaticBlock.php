<?php

namespace Abbott\MyAccount\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\BlockFactory;

class HeaderFooterStaticBlock implements DataPatchInterface, PatchVersionInterface
{
    private $moduleDataSetup;

    private $blockFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
    }

    public function apply()
    {

          $headerFooterStaticBlocks = [
            [
                'title' => 'Abbott Header',
                'identifier' => 'abbott_header',
                'content' => "",
                'is_active' => 1,
                'stores' => 1,
            ],
            [
                'title' => 'Abbott Footer',
                'identifier' => 'abbott_footer',
                'content' => "",
                'is_active' => 1,
                'stores' => 1,
            ],
            [
                'title' => 'Similac Header',
                'identifier' => 'similac_header',
                'content' => "",
                'is_active' => 1,
                'stores' => 3,
            ],
            [
                'title' => 'Similac Footer',
                'identifier' => 'similac_footer',
                'content' => "",
                'is_active' => 1,
                'stores' => 3,
            ],
            [
                'title' => 'Glucerna Header',
                'identifier' => 'glucerna_header',
                'content' => "",
                'is_active' => 1,
                'stores' => 2,
            ],
            [
                'title' => 'Glucerna Footer',
                'identifier' => 'glucerna_footer',
                'content' => "",
                'is_active' => 1,
                'stores' => 2,
            ]
            ];
          $this->moduleDataSetup->startSetup();
          foreach ($headerFooterStaticBlocks as $staticBlock) {
              $this->blockFactory->create()->setData($staticBlock)->save();
          }

          $this->moduleDataSetup->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public static function getVersion()
    {
        return '2.0.0';
    }

    public function getAliases()
    {
        return [];
    }
}
