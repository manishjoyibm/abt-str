<?php
namespace Abbott\ShoppingCart\Test\Integration\Helper;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Abbott\ShoppingCart\Helper\Data;

class CheckSubscriptionTest extends TestCase{

	/**
	* @var \Magento\Framework\ObjectManagerInterface
	*/
    private $objectManager;

	/**
	* @var Abbott\ShoppingCart\Helper\Data
	*/
    private $shoppingCartHelper;

    CONST TRUECARTITEMS  = array('0' =>  array('data' =>  array('sku' => 'GLUTRI12CTCHO', 'quantity' => '1',
     'aw_sarp2_subscription_type' => '10')) );

    CONST FALSECARTITEMS  = array('0' =>  array('data' =>  array('sku' => 'GLUTRI12CTCHO', 'quantity' => '1',
     'aw_sarp2_subscription_type' => '12')) );

	protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->shoppingCartHelper = $this->objectManager->create(Data::class); 
    }

    /**
     * Destroy Object
     */
    public function testcheckSubscriptionId()
    {
        
        $this->assertTrue($this->shoppingCartHelper->checkSubscriptionId(self::TRUECARTITEMS));

        $this->assertFalse($this->shoppingCartHelper->checkSubscriptionId(self::FALSECARTITEMS));
    }
}    