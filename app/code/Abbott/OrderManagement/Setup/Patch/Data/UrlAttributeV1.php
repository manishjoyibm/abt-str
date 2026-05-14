<?php

namespace Abbott\OrderManagement\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Variable\Model\VariableFactory;

class UrlAttributeV1 implements DataPatchInterface
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
                'code' => 'abbott_contactus_url',
                'name' => 'Abbott ContactUs',
                'html_value' => 'https://qa.abbottstore.com/en/contact-us.html',
            ],
            [
                'code' => 'glucerna_contactus_url',
                'name' => 'Glucerna ContactUs',
                'html_value' => 'https://qa.glucernastore.com/en/contact-us.html',
            ],
            [
                'code' => 'similac_contactus_url',
                'name' => 'Similac ContactUs',
                'html_value' => 'https://qa.similacstore.com/en/contact-us.html',
            ],
            [
                'code' => 'abbott_terms_of_sale',
                'name' => 'Abbott Terms of Sale',
                'html_value' => 'https://qa.abbottstore.com/en/terms-of-sale.html',
            ],
            [
                'code' => 'glucerna_terms_of_sale',
                'name' => 'Glucerna Terms of Sale',
                'html_value' => 'https://qa.glucernastore.com/en/terms-of-sale.html',
            ],
            [
                'code' => 'similac_terms_of_sale',
                'name' => 'Similac Terms of Sale',
                'html_value' => 'https://qa.similacstore.com/en/terms-of-sale.html',
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
