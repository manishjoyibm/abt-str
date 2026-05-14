<?php

namespace Abbott\OrderManagement\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Variable\Model\VariableFactory;

class UrlAttribute implements DataPatchInterface
{
    protected $variableFactory;

    public function __construct(VariableFactory $variableFactory)
    {
        $this->variableFactory = $variableFactory;
    }

    public function apply()
    {
        $variable = $this->variableFactory->create();
        $urlVariable = [
            [
                'code' => 'abbottstore_url',
                'name' => 'AbbottStore Url',
                'html_value' => 'https://dev-aem-dm-dispatcher.abbottstore.com/content/abbott/en.html',
            ],
            [
                'code' => 'glucernastore_url',
                'name' => 'GlucernaStore Url',
                'html_value' => 'https://dev-aem-dm-dispatcher.glucernastore.com/content/glucerna/en.html',
            ],
            [
                'code' => 'similacstore_url',
                'name' => 'SimilacStore Url',
                'html_value' => 'https://dev-aem-dm-dispatcher.similacstore.com/content/similac/en.html',
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
