<?php

declare(strict_types=1);

namespace Abbott\GigyaIM\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Validator\ValidateException;

class SimilacCustomerAttributesv1 implements DataPatchInterface, PatchVersionInterface
{
    public $attributeSetFactory;
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * Construct function
     *
     * @param CustomerSetupFactory $customerSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * Apply function
     *
     * @return void
     * @throws LocalizedException
     * @throws ValidateException
     */
    public function apply()
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributesInfo = [
            'user_type' => [
                'type'            => 'varchar',
                'label'           => 'User Type',
                'input'           => 'text',
                'required'        => 0,
                'is_used_in_grid' => 1,
                'user_defined'    => true,
                'position'        => 400,
                'system'          => 0,
                'visible'         => 1,
                'default'         => ''
            ]
        ];

        foreach ($attributesInfo as $attributeCode => $attributeParams) {
            $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, $attributeCode, $attributeParams);
        }

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $attributeName = ['user_type'];
        foreach ($attributeName as $attribute_value) {
            $attribute = $customerSetup->getEavConfig()->getAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                $attribute_value
            )
                          ->addData([
                              'attribute_set_id' => $attributeSetId,
                              'attribute_group_id' => $attributeGroupId,
                              'used_in_forms' => ['adminhtml_customer', 'customer_account_create'],
                          ]);
            $attribute->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '5.5.3';
    }

    /**
     * GetAliases function
     *
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }
}
