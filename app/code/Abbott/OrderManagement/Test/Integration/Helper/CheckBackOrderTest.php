<?php
namespace Abbott\OrderManagement\Test\Integration\Helper;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Abbott\OrderManagement\Helper\Data;

class CheckBackOrderTest extends TestCase
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Abbott\MyAccount\Helper\Data
     */
    private $helper;

    const TRUBACKORDERSKU  = '64918';

    const FALSBACKORDERSKU  = '62885P6';

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->helper = $this->objectManager->create(Data::class);
    }

    /**
     * Destroy Object
     */
    public function testCheckBackOrder()
    {

        $this->assertTrue($this->helper->checkBackOrder(self::TRUBACKORDERSKU));

        $this->assertFalse($this->helper->checkBackOrder(self::FALSBACKORDERSKU));
    }
}
