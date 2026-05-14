<?php

namespace Abbott\AdditionalAttributes\Setup\Patch\Data;

use Abbott\AdditionalAttributes\Model\Product\Attribute\Source\ProductAvailabilityOptions;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class SimilacAttributesv19 implements DataPatchInterface
{
    public const GROUP_NAME = 'AbbottStore';

    public const TABLE_NAME = 'catalog_product_entity_varchar';

    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * @var Attribute
     */
    private Attribute $eavAttribute;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param Attribute $attribute
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

    /**
     * Apply
     *
     * @throws LocalizedException
     */
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

    /**
     * Get Aliases
     *
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Get Dependencies
     *
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get Attributes List
     *
     * @return array[]
     */
    private function getAttributesList()
    {
        return [
            'formula_name' => [
                'group' => self::GROUP_NAME,
                'type' => 'text',
                'label' => 'Formula Name',
                'input' => 'multiselect',
                'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                'required' => false,
                'user_defined' => true,
                'unique' => false,
                'default' => null,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'searchable' => true,
                'filterable' => true,
                'comparable' => false,
                'visible_on_front' => true,
                'visible_in_advanced_search' => true,
                'used_in_product_listing' => true,
                'is_used_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_html_allowed_on_front' => false,
                'option' => [
                    'values' => $this->getAttributeOptions('formula_name')
                ]
            ],
            'product_availability' => [
                'group' => self::GROUP_NAME,
                'type' => 'varchar',
                'label' => 'Availability',
                'input' => 'select',
                'backend' => '',
                'required' => false,
                'user_defined' => true,
                'unique' => false,
                'default' => null,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'searchable' => true,
                'filterable' => true,
                'comparable' => false,
                'visible_on_front' => true,
                'visible_in_advanced_search' => true,
                'used_in_product_listing' => true,
                'is_used_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_html_allowed_on_front' => false,
                'option' => [
                    'values' => $this->getAttributeOptions('product_availability')
                ]
            ],
        ];
    }

    /**
     * Get Attribute Options
     *
     * @param string $attributeCode
     * @return array|string[]
     */
    private function getAttributeOptions(string $attributeCode): array
    {
        $options = [
            'formula_name' => [
                'Similac Bottles & Nipples', 'Similac 360 Total Care',
                'Pure Bliss By Similac', 'Similac Alimentum',
                'Similac Total Comfort', 'Similac Soy Isomil',
                'Similac Advance', 'Similac NeoSure',
                'Go & Grew by Similac', 'Similac Sensitive'
            ],
            'product_availability' => [
                'Show in stock items only'
            ]
        ];

        if ($attributeCode && isset($options[$attributeCode])) {
            return $options[$attributeCode];
        }
        return [];
    }
}
