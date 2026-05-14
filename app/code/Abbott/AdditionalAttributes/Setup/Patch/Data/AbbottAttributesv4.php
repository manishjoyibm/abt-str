<?php

namespace Abbott\AdditionalAttributes\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AbbottAttributesv4 implements DataPatchInterface
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
            'glucerna_associated_products' => [
                'group'        => 'Glucerna',
                'type'         => 'varchar',
                'label'        => 'Associated Products',
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
            'glucerna_product_plan' => [
                'group'        => 'Glucerna',
                'type'         => 'int',
                'source'       => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'label'        => 'Product Subscription Plan',
                'input'        => 'select',
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => true,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false,
                'comparable' => true,
                'is_html_allowed_on_front' => false,
                'filterable_in_search' => false
            ],
            'subscribe_customer_group' => [
                'group'        => 'Sarp2: Subscription Configuration',
                'type'         => 'varchar',
                'backend'      => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                'label'        => 'Customer Group for discount',
                'input'        => 'multiselect',
                'used_in_product_listing' => false,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => false,
                'required'     => false,
                'comparable'=> false,
                'is_html_allowed_on_front' => false,
                'is_used_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        ];
        foreach ($attributes as $attributeCode => $attributeParam) {
            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, $attributeParam);
        }

        $subscription_attribute = $eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'glucerna_product_plan');
        $subscription_options = [
            'values' => ["Select Subscription Plan","Individual 30 bottles","Individual 6 bottles (Trial)","Family 12 Bottles (Trial)","Family 60 Bottles"],
                'attribute_id' => $subscription_attribute,
        ];

        $subscription_customer_group_attribute = $eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'subscribe_customer_group');
        $subscription_customer_group_options = [
            'values' => ["NOT LOGGED IN","Consumer","Employee","Sports","Nepro Dialysis","NSF","Small Retail","Military","EAS Small Gyms","Omnipod","AlaskaDialysis","KentuckyOne","ACP_UAT","Bcp","Acp","QA_1","QA_SIT88","my group","BCP1","ccp","TEST 1911","mydemogroup","DaraTest","MedicalGroupTest","juvendisplay","Retiree","acp-sit"],
                'attribute_id' => $subscription_customer_group_attribute,
        ];

        $eavSetup->addAttributeOption($subscription_options);
        $eavSetup->addAttributeOption($subscription_customer_group_options);
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
