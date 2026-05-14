<?php

namespace Abbott\AdditionalAttributes\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AbbottAttributesv17 implements DataPatchInterface
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
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['$setup' => $this->moduleDataSetup]);

        $attributes = [
            'need_state' => [
                'group'        => 'AbbottStore',
                'type'         => 'varchar',
                'label'        => 'Need State',
                'input'        => 'text',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
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
        return [AbbottAttributesv15::class];
    }
}
