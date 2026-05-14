<?php

namespace Abbott\PedialyteCart\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Variable\Model\VariableFactory;

class PedialyteUrlAttribute implements DataPatchInterface 
{
    protected $variableFactory;

    public function __construct(VariableFactory $variableFactory)
    {
        $this->variableFactory = $variableFactory;
    }
    
    public function apply() {
        $variable = $this->variableFactory->create();
        $urlVariable = [
            [
                'code' => 'pedialyte_url',
                'name' => 'Pedialyte Url',
                'html_value' => '',
            ],
            [
                'code' => 'pedialyte_plp_url',
                'name' => 'Pedialyte Product Listing Page',
                'html_value' => '',
            ],
            [
                'code' => 'pedialyte_contactus_url',
                'name' => 'Pedialyte ContactUs',
                'html_value' => '',
            ]
            
        ];
        
        foreach ($urlVariable as $variableCode) {
            $variableExists = $this->createVariable()->loadByCode($variableCode['code']);
            $variableExistsId = $variableExists->getId();
            if (!$variableExistsId) {
                $variable->setData($variableCode);
                $variable->save();  
            }
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
    private function createVariable() 
    {
        return $this->variableFactory->create();
    }
}
