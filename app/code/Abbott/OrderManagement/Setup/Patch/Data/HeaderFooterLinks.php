<?php

namespace Abbott\OrderManagement\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Variable\Model\VariableFactory;

class HeaderFooterLinks implements DataPatchInterface
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
                'code' => 'privacy_policy',
                'name' => 'Privacy Policy',
                'html_value' => 'https://www.abbott.com/privacy-policy.html',
            ],
            [
                'code' => 'terms_and_conditions',
                'name' => 'Terms and Conditions',
                'html_value' => 'https://www.abbott.com/online-terms-and-conditions.html',
            ],
            [
                'code' => 'abbott_url',
                'name' => 'Abbott.com Url',
                'html_value' => 'https://www.abbott.com',
            ],
            [
                'code' => 'similac_privacy_policy',
                'name' => 'Similac Privacy Policy',
                'html_value' => 'https://www.abbott.com/privacy-policy.html',
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
