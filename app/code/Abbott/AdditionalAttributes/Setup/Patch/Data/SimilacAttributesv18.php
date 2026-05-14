<?php

namespace Abbott\AdditionalAttributes\Setup\Patch\Data;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Psr\Log\LoggerInterface;

class SimilacAttributesv18 implements DataPatchInterface
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
     * @param CollectionFactory $productCollection
     * @param LoggerInterface $logger
     * @param \Magento\Framework\App\State $state
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
     * @return SimilacAttributesv18|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
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
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return string[]
     */
    public static function getDependencies()
    {
        return [SimilacAttributesv17::class];
    }

    /**
     * @return array[]
     */
    private function getAttributesList()
    {
        return [
            'disable_sale' => [
                'group' => self::GROUP_NAME,
                'type'         => 'int',
                'source'       => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'label'        => 'Disable Sale',
                'input'        => 'boolean',
                'global'       => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => true,
                'user_defined' => true,
                'visible'      => true,
                'searchable'   => false,
                'filterable'   => false,
                'visible_on_front' => true,
                'required'     => false,
                'is_used_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_html_allowed_on_front' => true,
                'default' => 0
            ]
        ];
    }
}
