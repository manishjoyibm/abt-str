<?php

namespace Abbott\MyAccount\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\BlockFactory;

class RegisterContentUpdate implements DataPatchInterface, PatchVersionInterface
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
        $abbott_contact_preference = "I'd like to receive news and information from AbbottStore and Abbott. I understand that the information I've provided will be used by Abbott and its contracted service providers to provide me with helpful information about AbbottStore and to send me marketing materials and promotional offers by email or regular mail about Abbott's products and services. To be removed from AbbottStore's mailing list, contact 1-800-258-7677.";

        $glucerna_contact_preference = "I'd like to receive news and information from GlucernaStore, Glucerna and Abbott. I understand that the information I've provided will be used by Abbott and its contracted service providers to provide me with helpful information about GlucernaStore and to send me marketing materials and promotional offers by email or regular mail about Abbott's products and services. To be removed from GlucernaStore's mailing list, contact 1-877-457-0524.";
              

        $registerAdditionalContactBlockContents = [
            [
                'title' => 'Abbott Contact Preference',
                'identifier' => 'abbott_contact_preference',
                'content' => $abbott_contact_preference,
                'is_active' => 1,
                'stores' => 1,
            ],
            [
                'title' => 'Glucerna Contact Preference',
                'identifier' => 'glucerna_contact_preference',
                'content' => $glucerna_contact_preference,
                'is_active' => 1,
                'stores' => 2,
            ]
            ];
        $this->moduleDataSetup->startSetup();
        foreach ($registerAdditionalContactBlockContents as $registerAdditionalBlocks) {

            $updateBlock = $this->blockFactory->create()->load(
                $registerAdditionalBlocks['identifier'],
                'identifier'
            );
            if ($updateBlock->getId()) {
                $updateBlock->setContent($registerAdditionalBlocks['content']);
                $updateBlock->save();
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
        return '3.0.0';
    }

    public function getAliases()
    {
        return [];
    }
}
