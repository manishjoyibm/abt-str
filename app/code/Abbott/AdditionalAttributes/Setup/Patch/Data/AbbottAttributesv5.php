<?php

namespace Abbott\AdditionalAttributes\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AbbottAttributesv5 implements DataPatchInterface
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
             'glucerna_funnel_index' => [
                'group'        => 'Glucerna',
                'type'         => 'varchar',
                'label'        => 'Funnel Index',
                'input'        => 'text',
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => true,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => true,
                'is_used_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_html_allowed_on_front' => false
             ],
             'group_sku' => [
                'group'        => 'AbbottStore',
                'type'         => 'varchar',
                'label'        => 'SKU of available flavors',
                'input'        => 'textarea',
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => true,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false,
                'comparable' => false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => true,
                'is_used_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_html_allowed_on_front' => true
             ],
             'glucerna_delivery_split' => [
                'group'        => 'Glucerna',
                'type'         => 'varchar',
                'label'        => 'Delivery Split',
                'input'        => 'text',
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => true,
                'is_used_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_html_allowed_on_front' => false
             ],
             'actual_trial_sku_mapping' => [
                'group'        => 'Glucerna',
                'type'         => 'varchar',
                'label'        => 'Trial SKU Mapping',
                'input'        => 'text',
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => true,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false,
                'comparable'=> true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => true,
                'is_used_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_html_allowed_on_front' => false
             ],
             'glucerna_backorder_date' => [
                'group'        => 'Glucerna',
                'type'         => 'datetime',
                'label'        => 'Backorder Date',
                'input'        => 'date',
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false,
                'comparable'=> false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => true,
                'is_used_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_html_allowed_on_front' => false
             ],
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
