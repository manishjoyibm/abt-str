<?php


namespace Abbott\GPAS\Setup\Patch\Data;

use Abbott\GPAS\Model\Attribute\Customer\QrCodeId;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;

class QrCodeIdAttribute implements DataPatchInterface
{
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * QrCodeIdAttribute constructor.
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
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
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributesInfo = [
            QrCodeId::ATTRIBUTE_CODE => [
                'type' => 'int',
                'label' => 'QR Code ID',
                'input' => 'text',
                'required' => 0,
                'is_used_in_grid' => 1,
                'user_defined' => false,
                'position' => 999,
                'system' => 0,
                'visible' => 0,
            ]
        ];

        foreach ($attributesInfo as $attributeCode => $attributeParams) {
            $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, $attributeCode, $attributeParams);
        }
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $attributeName = [QrCodeId::ATTRIBUTE_CODE];
        foreach ($attributeName as $attribute_value) {
            $attribute = $customerSetup->getEavConfig()->getAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                $attribute_value
            )
                ->addData([
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms' => ['adminhtml_customer'],
                ]);
            $attribute->save();
        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * GetDependencies function
     *
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
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
