<?php

namespace Abbott\PedialyteCart\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Store\Api\StoreRepositoryInterface;
use Psr\Log\LoggerInterface;

class PedialyteHeaderFooterStaticBlock implements DataPatchInterface, PatchVersionInterface
{
    private $moduleDataSetup;

    private $blockFactory;
    protected $storeRepository;
    private $logger;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory,
        StoreRepositoryInterface $storeRepository,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
        $this->storeRepository = $storeRepository;
        $this->logger = $logger;
    }

    public function apply()
    {
        try {
            $pdlStore = $this->storeRepository->get('pedialyte');
            $pdlStoreId = $pdlStore->getId();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->logger->critical($e->getMessage());
        }

          $headerFooterStaticBlocks = [
            [
                'title' => 'Pedialyte Header',
                'identifier' => 'pedialyte_header',
                'content' => "",
                'is_active' => 1,
                'stores' => $pdlStoreId,
            ],
            [
                'title' => 'Pedialyte Footer',
                'identifier' => 'pedialyte_footer',
                'content' => "",
                'is_active' => 1,
                'stores' => $pdlStoreId,
            ]
            ];
          $this->moduleDataSetup->startSetup();
          foreach ($headerFooterStaticBlocks as $staticBlock) {
          $blockExists = $this->createBlock()->load(
            $staticBlock['identifier'],
            'identifier'
                    );
                    $blockExistsId = $blockExists->getId();
            if (!$blockExistsId) {
                $this->blockFactory->create()->setData($staticBlock)->save();
            }
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
    private function createBlock() {
        return $this->blockFactory->create();
    }
}
