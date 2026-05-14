<?php


namespace Abbott\GPAS\Setup\Patch\Data;


use Abbott\GPAS\Model\Attribute\Product\IsGpas;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory as EavSetupFactory;

/**
 * Class IsGPASAttribute
 * @package Abbott\GPAS\Setup\Patch\Data
 */
class IsGPASAttribute implements DataPatchInterface
{


    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;


    /**
     * IsGPASAttribute constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['$setup' => $this->moduleDataSetup]);

        $attributes = [
            IsGpas::ATTRIBUTE_CODE => [
                'group'        => 'AbbottStore',
                'type'         => 'int',
                'label'        => 'Is GPAS Only?',
                'input'        => 'boolean',
                'source'       => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => true,
                'required'     => false,
                'is_used_in_grid' => true
            ],
        ];

        foreach ($attributes as $attributeCode => $attributeParam) {
            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, $attributeParam);
        }


        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }
}
