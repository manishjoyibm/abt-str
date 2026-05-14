<?php

namespace Abbott\Pndp\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class PndpAttribute implements DataPatchInterface
{

    /* @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /* @var EavSetupFactory */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['$setup' => $this->moduleDataSetup]);

        $attributes = [
            'is_pndp' => [
                'group'        => 'AbbottStore',
                'type'         => 'int',
                'label'        => 'Is PNDP',
                'input'        => 'boolean',
                'source'       => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'used_in_product_listing' => true,
                'user_defined' => false,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => true,
                'required'     => false,
                'is_used_in_grid' => true
            ]
        ];

        foreach ($attributes as $attributeCode => $attributeParam) {
            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, $attributeParam);
        }
    }

    public function getAliases()
    {
        return [];
    }

    public static function getDependencies()
    {
        return [];
    }
}
