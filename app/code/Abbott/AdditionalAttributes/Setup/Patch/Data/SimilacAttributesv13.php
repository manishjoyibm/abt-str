<?php

namespace Abbott\AdditionalAttributes\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class SimilacAttributesv13 implements DataPatchInterface
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
            'age' => [
                'group' => self::GROUP_NAME,
                'type' => 'varchar',
                'label' => 'Age',
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
                    'values' => $this->getAttributeOptions('age')
                ]
            ],
            'formula_type' => [
                'group' => self::GROUP_NAME,
                'type' => 'varchar',
                'label' => 'Formula Type',
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
                    'values' => $this->getAttributeOptions('formula_type')
                ]
            ],
            'features' => [
                'group' => self::GROUP_NAME,
                'type' => 'varchar',
                'label' => 'Features',
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
                    'values' => $this->getAttributeOptions('features')
                ]
            ],
            'supplies' => [
                'group' => self::GROUP_NAME,
                'type' => 'varchar',
                'label' => 'Supplies',
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
                    'values' => $this->getAttributeOptions('supplies')
                ]
            ]
        ];
    }

    private function getAttributeOptions($attributeCode)
    {
        $options = [
            'age' => [
                'Preemie', 'Baby', 'Toddler 12-36 months'
            ],
            'formula_type' => [
                'Standard', 'For Food Allergies', 'Organic',
                'For Supplementation', 'For Spit Up',
                'For Crying and Colic Symptoms', 'For Fussiness or Gas'
            ],
            'features' => [
                "2'‐FL HMO", "For Lactose Sensitivity and Easy Digestion",
                "Non‐GMO", "Soy", "Kosher", "Badatz‐Certified", "A2 Milk"
            ],
            'supplies' => [
                "Bottles & Nipples", "Hydration"
            ]
        ];

        if ($attributeCode && isset($options[$attributeCode])) {
            return $options[$attributeCode];
        }
        return [];
    }
}
