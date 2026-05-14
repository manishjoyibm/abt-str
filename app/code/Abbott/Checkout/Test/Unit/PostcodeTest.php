<?php

namespace Abbott\Checkout\Test\Unit;

use Abbott\Checkout\Model\Attribute\Data\Plugin\Postcode;

class PostcodeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    public $objectManager;
    public $postCode;
    /**
     * @var (\Abbott\Checkout\Model\Attribute\Data\Plugin\Postcode & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $block;
    /**
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->postCode = $this->objectManager->getObject(Postcode::class);
        $this->block = $this->getMockBuilder(Postcode::class)
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return void
     */
    public function testValidateZipCode()
    {
        $attributeMock = $this->createMock(\Magento\Eav\Model\Attribute::class);
        $result1 = $this->postCode->validateZipCode($attributeMock, '1245-7890');
        $result2 = $this->postCode->validateZipCode($attributeMock, '12345-7890');
        $result3 = $this->postCode->validateZipCode($attributeMock, '1245-');
        $result4 = $this->postCode->validateZipCode($attributeMock, '12345');
        $this->assertEquals(1, count($result1));
        $this->assertEquals(0, count($result2));
        $this->assertEquals(1, count($result3));
        $this->assertEquals(0, count($result4));
    }
}
