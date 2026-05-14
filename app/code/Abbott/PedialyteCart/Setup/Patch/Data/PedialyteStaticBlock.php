<?php

namespace Abbott\PedialyteCart\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Store\Api\StoreRepositoryInterface;
use Psr\Log\LoggerInterface;

class PedialyteStaticBlock implements DataPatchInterface, PatchVersionInterface
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
          $pedialyte_contact_preference = "I’d like to receive news and information from Pedialyte and Abbott. I understand that the information I've provided will be used by Abbott and its contracted service providers to provide me with helpful information about Pedialyte and to send me marketing materials and promotional offers by email or regular mail about Abbott's products and services.";
          $pedialyteStaticBlocks = [
            [
                'title' => 'Pedialyte Contact Preference',
                'identifier' => 'pedialyte_contact_preference',
                'content' => $pedialyte_contact_preference,
                'is_active' => 1,
                'stores' => $pdlStoreId,
            ]
            ];
          $this->moduleDataSetup->startSetup();
          foreach ($pedialyteStaticBlocks as $staticBlock) {
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
    private function createBlock() 
    {
        return $this->blockFactory->create();
    }
}
