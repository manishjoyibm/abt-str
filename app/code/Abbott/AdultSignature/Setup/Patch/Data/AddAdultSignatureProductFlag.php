<?php
declare(strict_types=1);

namespace Abbott\AdultSignature\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Data patch to add the product attribute 'abbott_requires_adult_signature'.
 *
 * @category  Abbott
 * @package   Abbott_AdultSignature
 */
class AddAdultSignatureProductFlag implements DataPatchInterface
{
    /** @var ModuleDataSetupInterface */
    private ModuleDataSetupInterface $moduleDataSetup;

    /** @var EavSetupFactory */
    private EavSetupFactory $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup Module data setup
     * @param EavSetupFactory $eavSetupFactory EAV setup factory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Apply patch to create product attribute.
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'abbott_requires_adult_signature',
            [
                'type' => 'int',
                'label' => 'Requires Adult Signature',
                'input' => 'boolean',
                'default' => 0,
                'required' => false,
                'user_defined' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'group'  => 'AbbottStore',
                'visible_on_front' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'used_in_product_listing' => true,
                'sort_order'                 => 209
            ]
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /** @inheritDoc */
    public static function getDependencies(): array
    {
        return [];
    }

    /** @inheritDoc */
    public function getAliases(): array
    {
        return [];
    }
}
