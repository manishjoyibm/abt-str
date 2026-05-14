<?php
declare(strict_types=1);

namespace Abbott\AdultSignature\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;

class AddShippingStateAttributeForAdultSignature implements DataPatchInterface
{
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private EavSetupFactory $eavSetupFactory
    ) {}

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $code = 'abbott_shipping_state_adult_signature';

        // If re-running patch, skip if already exists
        $attr = $eavSetup->getAttribute(Product::ENTITY, $code);
        if (!$attr) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                $code,
                [
                    'type'                       => 'varchar', // multiselect stored as CSV
                    'label'                      => 'Shipping States For Adult Signature',
                    'input'                      => 'multiselect',
                    'backend'                    => ArrayBackend::class, // handles CSV storage
                    'source'                     => \Abbott\AdultSignature\Model\Attribute\Source\States::class,
                    'required'                   => false,
                    'user_defined'               => true,
                    'global'                     => ScopedAttributeInterface::SCOPE_WEBSITE,
                    'visible'                    => true,
                    'visible_on_front'           => false,
                    'is_html_allowed_on_front'   => false,
                    'searchable'                 => false,
                    'filterable'                 => false,
                    'comparable'                 => false,
                    'unique'                     => false,
                    'apply_to'                   => '', // all product types
                    'used_in_product_listing'    => true,
                    'group'                      => 'AbbottStore', // or 'General'
                    'sort_order'                 => 210
                ]
            );
        }

        // Optionally add to all attribute sets/groups:
        $attributeId = (int)$eavSetup->getAttributeId(Product::ENTITY, $code);
        if ($attributeId) {
            $entityTypeId = (int)$eavSetup->getEntityTypeId(Product::ENTITY);
            $setIds = $eavSetup->getAllAttributeSetIds($entityTypeId);
            foreach ($setIds as $setId) {
                // Try to add into Product Details group; fallback to General group
                $groupId = $eavSetup->getAttributeGroupId($entityTypeId, $setId, 'AbbottStore')
                    ?: $eavSetup->getDefaultAttributeGroupId($entityTypeId, $setId);

                $eavSetup->addAttributeToGroup($entityTypeId, $setId, $groupId, $attributeId, 210);
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies(): array { return []; }
    public function getAliases(): array { return []; }
}
