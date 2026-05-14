<?php

namespace Abbott\AdditionalAttributes\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class SimilacAttributes implements DataPatchInterface
{
    const GROUP_NAME = 'Search Engine Optimization';

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
            'aem_url_key' => [
                'group' => self::GROUP_NAME,
                'type' => 'varchar',
                'label' => 'AEM Url Key',
                'input' => 'text',
                'required' => false,
                'user_defined' => true,
                'unique' => false,
                'default' => null,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'visible_in_advanced_search' => false,
                'used_in_product_listing' => false,
                'is_used_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_html_allowed_on_front' => false,
                'sort_order' => 1,
            ]
        ];
    }
}
