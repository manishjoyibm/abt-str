<?php

namespace Abbott\TaxExempt\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class CustomerAttribute implements DataPatchInterface, PatchVersionInterface
{

    /**
     * @var AttributeSetFactory
     */
    public AttributeSetFactory $attributeSetFactory;
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
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory     $customerSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup,
        AttributeSetFactory      $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply(): void
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributesInfo = [
            'tax_exempt_file' => [
                'type'         => 'varchar',
                'label' => 'Tax Exempt Certificate',
                'input'        => 'file',
                'backend' => null,
                'required'     => 0,
                'is_used_in_grid' => 1,
                'user_defined' => true,
                'position'     => 1011,
                'system'       => 0,
                'visible'      => 1,
            ],
            'tax_exempt_number' => [
                'type'         => 'varchar',
                'label'        => 'Tax Exempt Certificate Id',
                'input'        => 'text',
                'required'     => 0,
                'is_used_in_grid' => 1,
                'user_defined' => true,
                'position'     => 1012,
                'system'       => 0,
                'visible'      => 1,
                'default'    => null
            ],
            'tax_certificate_date' => [
                'type'         => 'datetime',
                'label'        => 'Tax Exempt Certificate Expiry Date',
                'input'        => 'date',
                'class' => 'validate-date',
                'required'     => 0,
                'is_used_in_grid' => 1,
                'is_filterable_in_grid' => 1,
                'is_searchable_in_grid' => 1,
                'user_defined' => true,
                'position'     => 1013,
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

        $attributeName = ['tax_exempt_file','tax_exempt_number','tax_certificate_date'];
        foreach ($attributeName as $attribute_value) {
            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $attribute_value)
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
    public function getAliases()
    {
        return [];
    }
}
