<?php
namespace Abbott\MyAccount\Test\Integration\Helper;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Abbott\MyAccount\Helper\Data;

class verifyDomainTest extends TestCase{

	/**
	* @var \Magento\Framework\ObjectManagerInterface
	*/
    private $objectManager;

	/**
	* @var Abbott\MyAccount\Helper\Data
	*/
    private $helper;

    CONST TRUEEMAIL  = 'test@gmail.com';

    CONST FALSEEMAIL  = 'test@10mail.org';

	protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->helper = $this->objectManager->create(Data::class); 
    }

    /**
     * Destroy Object
     */
    public function testverifyDomain()
    {
        
        $this->assertTrue($this->helper->verifyDomain(self::FALSEEMAIL));

        $this->assertFalse($this->helper->verifyDomain(self::TRUEEMAIL));
    }
}    