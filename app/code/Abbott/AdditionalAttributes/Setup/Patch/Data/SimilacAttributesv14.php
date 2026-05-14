<?php

declare(strict_types=1);

namespace Abbott\AdditionalAttributes\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class SimilacAttributesv14 implements DataPatchInterface
{
    public $PlanFactory;
    const GROUP_NAME = 'Similac';

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
        Attribute $attribute,            
        \Aheadworks\Sarp2\Model\PlanFactory $planFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavAttribute = $attribute;
        $this->PlanFactory = $planFactory;        
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
            'plans' => [
                'group' => self::GROUP_NAME,
                'type' => 'varchar',
                'label' => 'Plans',
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
                    'values' => $this->getAttributeOptions('plans')
                ]
            ]
        ];        
    }

    private function getAttributeOptions($attributeCode)
    {
        $plans = $this->PlanFactory->create()->getCollection()
                ->addFieldToSelect(['plan_id','name'])
                ->addFieldToFilter('status', ['eq' => 1])
                ->getData();
        $plansList = [];
                if(count($plans) > 0 && !empty($plans)){
                        foreach($plans as $plan){
                               $plansList[] = $plan['plan_id'];
                        }
                }
                $options = [
            'plans' => $plansList
        ];
        if ($attributeCode && isset($options[$attributeCode])) {
            return $options[$attributeCode];
        }
        return [];
    }
}
