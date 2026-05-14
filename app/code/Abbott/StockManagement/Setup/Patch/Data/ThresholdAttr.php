<?php

namespace Abbott\StockManagement\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ThresholdAttr implements DataPatchInterface
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
             'threshold' => [
                'group'        => 'AbbottStore',
                'type'         => 'int',
                'label'        => 'Threshold',
                'input'        => 'text',
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible_in_advanced_search' => false,
                'is_used_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_html_allowed_on_front' => false
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
