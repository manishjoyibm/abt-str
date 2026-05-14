<?php

namespace Abbott\AdditionalAttributes\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class SimilacAttributesv1 implements DataPatchInterface
{
    const GROUP_NAME = 'AbbottStore';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var Attribute
     */
    private $eavAttribute;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param Attrribute $attribute
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        Attribute $attribute
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavAttribute = $attribute;
    }

    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $attributes = $this->getAttributesList();
        foreach ($attributes as $attributeCode => $attributeParam) {
            if (!$this->eavAttribute->getIdByCode('catalog_product', $attributeCode)) {
                $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, $attributeParam);
            }
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

    private function getAttributesList()
    {
        return [
            'amazon_purchase' => [
				'group'        => self::GROUP_NAME,
                'type'         => 'int',
                'source'       => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'label'        => 'Product available only on Amazon',
                'input'        => 'boolean',
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible_in_advanced_search' => false,
                'is_used_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_html_allowed_on_front' => false
            ]
        ];
    }
}
