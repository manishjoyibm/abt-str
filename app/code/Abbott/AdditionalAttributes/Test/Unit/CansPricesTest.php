<?php

namespace Abbott\AdditionalAttributes\Test\Unit;

class CansPricesTest extends \PHPUnit\Framework\TestCase
{
    public $block;
    public function setUp()
    {
        $this->block = $this->getMockBuilder(\Abbott\AdditionalAttributes\Model\Resolver\Product\CansPrices::class)->disableOriginalConstructor()->getMock();
    }

    public function testCanPrice()
    {
        $testMethod = new \ReflectionMethod(\Abbott\AdditionalAttributes\Model\Resolver\Product\CansPrices::class, 'getCanPrice');
        $testMethod->setAccessible(true);
        $this->assertEquals(145.96, $testMethod->invokeArgs($this->block, [36.49,"select 4 cans"]));
        $this->assertNotEquals(145.96, $testMethod->invokeArgs($this->block, [36.49,"select 2 cans"]));
    }

    public function testCanDiscPrice()
    {
        $testMethod = new \ReflectionMethod(\Abbott\AdditionalAttributes\Model\Resolver\Product\CansPrices::class, 'getCanDiscPrice');
        $testMethod->setAccessible(true);
        $this->assertEquals(131.36, $testMethod->invokeArgs($this->block, [145.96,10]));
        $this->assertNotEquals(145.96, $testMethod->invokeArgs($this->block, [145.96,10]));
    }
}
