<?php

namespace Abbott\PedialyteCart\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Variable\Model\VariableFactory;

class PedialyteCopyrightAttribute implements DataPatchInterface 
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
                'code' => 'pedialyte_email_footer_copyright',
                'name' => 'Pedialyte Email Footer Copy Right',
                'html_value' => '<span style="color:#999; font-family: Georgia, serif, Arial, sans-serif;font-size:10.5pt; float: right;">© 2024 Abbott</span>',
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
