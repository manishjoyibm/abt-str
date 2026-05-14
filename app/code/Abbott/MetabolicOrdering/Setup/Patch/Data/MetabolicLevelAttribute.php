<?php

namespace Abbott\MetabolicOrdering\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MetabolicLevelAttribute implements DataPatchInterface
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
            'pre_approval' => [
                'group'        => 'AbbottStore',
                'type'         => 'int',
                'source'       => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                'label'        => 'HCP Pre-approval required',
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

        $metabolicAttributeId = $eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'pre_approval');

        $options = [
            'values' => ['Level1','Level2'],
            'attribute_id' => $metabolicAttributeId,
        ];

        $eavSetup->addAttributeOption($options);
    }

    public function getAliases()
    {
        return [];
    }

    /**
     * GetDependencies function
     *
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }
}
