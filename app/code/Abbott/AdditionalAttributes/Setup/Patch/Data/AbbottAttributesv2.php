<?php

namespace Abbott\AdditionalAttributes\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AbbottAttributesv2 implements DataPatchInterface
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
            'custom_discount' => [
                'group'        => 'AbbottStore',
                'type'         => 'int',
                'label'        => 'Product Custom Discount For Display',
                'input'        => 'text',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => true,
                'required'     => false,
                'frontend_class' => 'validate-digits'
            ],
            'case_of_product' => [
                'group'        => 'AbbottStore',
                'type'         => 'varchar',
                'label'        => 'Case of product',
                'input'        => 'text',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'used_in_product_listing' => false,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false,
                'is_used_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_html_allowed_on_front' => true
            ],
            'cases' => [
                'group'        => 'AbbottStore',
                'type'         => 'int',
                'source'       => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                'label'        => 'Sizes',
                'input'        => 'select',
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => true,
                'filterable'   => true,
                'visible_on_front' => true,
                'required'     => false,
                'comparable'=> true,
                'is_html_allowed_on_front' => true,
                'is_used_in_grid' => true,
                'is_filterable_in_grid' => true,
                'filterable_in_search' => true
            ]
        ];
        foreach ($attributes as $attributeCode => $attributeParam) {
            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, $attributeParam);
        }

        $cases_attributeId = $eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'cases');

        $cases_options = [
            'values' => ['Case of 2','Case of 60','3-4 pks','Case of 4','6-6 Packs','Case of 12','Case of 6-6 ct Cartons','Case of 36','36 count','Case of 30','Case of 24','Case of 16','Case of 6','Case of 48','2-pack','25 count','50 count','1 Pack','Case of 8','Case of 64','8-8 Packs','Case of 144','Case of 100','10-pack','4-10 packs','Case of 150','Case of 250','Case of 50','2 lb container','1.7 lb canister','2 lb canister','6-pack','14 Count','Case of 20','1.65 lb tub','6 ct Carton','12 Pack','30 day supply','90 day supply','100-pack','Pack of 50','Pack of 8','Case of 15','8-pack','30-pack','1 Bag','6-pack of each (12 total)','Case of 3','2-10 fl oz bottles & 15-8 fl oz cartons','8 Pack','12 Count','1 Starter Pack','320g Tub / Case of 4','320g Tub','4-16 packs','Small','Medium','Large','X-Large','3-10 fl oz bottles & 10-8 fl oz cartons','3-10 fl oz bottles & 20-8 fl oz cartons','3-10 fl oz bottles & 28-8 fl oz cartons','3-10 fl oz bottles & 56-8 fl oz cartons','Pack of 4','Case of 80','1 L bottle','6 Pack','4 Pack','3 Pre-Surgery Drinks & 56 Immunonutrition Shakes & 4 Ensure Enlive Shakes','3 Pre-Surgery Drinks & 56 Immunonutrition Shakes & 6 Glucerna Hunger Smart Shakes','3 Pre-Surgery Drinks & 28 Immunonutrition Shakes & 4 Ensure Enlive Shakes','3 Pre-Surgery Drinks & 28 Immunonutrition Shakes & 6 Glucerna Hunger Smart Shakes'],
            'attribute_id' => $cases_attributeId,
        ];

        $eavSetup->addAttributeOption($cases_options);
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
