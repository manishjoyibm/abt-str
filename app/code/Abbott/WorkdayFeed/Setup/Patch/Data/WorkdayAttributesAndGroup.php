<?php

namespace Abbott\WorkdayFeed\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class WorkdayAttributesAndGroup implements DataPatchInterface, PatchVersionInterface
{
    public $attributeSetFactory;
    /**
     * @var CustomerSetupFactory
     */
    private CustomerSetupFactory $customerSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
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
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // insert Employee customer group
        $this->moduleDataSetup->getConnection()->insertForce(
            $this->moduleDataSetup->getTable('customer_group'),
            ['customer_group_id' => 4, 'customer_group_code' => 'Employee', 'tax_class_id' => 3]
        );

        $attributesInfo = [
            'wd_company' => [
                'type'         => 'varchar',
                'label'        => 'Workday Company',
                'input'        => 'text',
                'required'     => 0,
                'is_used_in_grid' => 1,
                'user_defined' => true,
                'position'     => 999,
                'system'       => 0,
                'visible'      => 0,
            ],
            'wd_status' => [
                'type'         => 'varchar',
                'label'        => 'Workday Status',
                'input'        => 'text',
                'required'     => 0,
                'is_used_in_grid' => 1,
                'user_defined' => true,
                'position'     => 1000,
                'system'       => 0,
                'visible'      => 0,
            ],
            'wd_upi' => [
                'type'         => 'varchar',
                'label'        => 'Workday UPI',
                'input'        => 'text',
                'required'     => 0,
                'is_used_in_grid' => 1,
                'is_filterable_in_grid' => 1,
                'is_searchable_in_grid' => 1,
                'user_defined' => true,
                'position'     => 1001,
                'system'       => 0,
                'visible'      => 1,
            ]

        ];

        foreach ($attributesInfo as $attributeCode => $attributeParams) {
            $customerSetup->addAttribute(Customer::ENTITY, $attributeCode, $attributeParams);
        }

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $attributeName = ['wd_company','wd_status','wd_upi'];
        foreach ($attributeName as $attribute_value) {
            $attribute = $customerSetup->getEavConfig()->getAttribute(
                Customer::ENTITY,
                $attribute_value
            )
                          ->addData([
                              'attribute_set_id' => $attributeSetId,
                              'attribute_group_id' => $attributeGroupId,
                              'used_in_forms' => ['adminhtml_customer'],
                          ]);
            $attribute->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion(): string
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}
