<?php

namespace Abbott\AdditionalAttributes\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AbbottAttributes12 implements DataPatchInterface
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
            'cans_y_min_update' => [
                'group'        => 'AbbottStore',
                'type'         => 'int',
                'label'        => 'Minimum value allowed for update QTY',
                'input'        => 'text',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => true,
                'required'     => false,
                'default'      => '2'
            ],
            'cans_x_max_update' => [
                'group'        => 'AbbottStore',
                'type'         => 'int',
                'label'        => 'Maximum value allowed for update QTY',
                'input'        => 'text',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => true,
                'required'     => false,
                'default'      => '6'
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
