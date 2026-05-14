<?php

namespace Abbott\OrderManagement\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Variable\Model\VariableFactory;

class UrlAttributeV2 implements DataPatchInterface
{
    /* @var VariableFactory */
    protected $variableFactory;

    /**
     * @param VariableFactory $variableFactory
     */
    public function __construct(VariableFactory $variableFactory)
    {
        $this->variableFactory = $variableFactory;
    }

    public function apply()
    {
        $variable = $this->variableFactory->create();
        $urlVariable = [
            [
                'code' => 'glucerna_privacy_policy',
                'name' => 'Glucerna Privacy Policy',
                'html_value' => 'https://www.abbottnutrition.com/privacy-policy',
            ]
        ];
        foreach ($urlVariable as $variableCode) {
            $variable->setData($variableCode);
            $variable->save();
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
}
