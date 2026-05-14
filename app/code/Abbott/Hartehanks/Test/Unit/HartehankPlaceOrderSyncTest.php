<?php

namespace Abbott\Hartehanks\Test\Unit;

class HartehankPlaceOrderSyncTest extends \PHPUnit\Framework\TestCase
{
    public $block;
    public $failureOutput;
    public $successOutput;
    public function setUp()
    {
        $this->block = $this->getMockBuilder(\Abbott\Hartehanks\Model\HartehankPlaceOrderSync::class)->disableOriginalConstructor()->getMock();
        $this->block->failCounter = 0;
        $this->block->succesCounter = 1;
        $this->failureOutput = ['lastname &','firstname','test.fail@abbott.com','01234-2222','street','city','USA','123456789'];
        $this->successOutput = ['firstname &amp;','lastname &quot;','test.mail@abbott.com','02351-2111','&quot;777 Brockton &amp; Ave&quot;','Abington','US','7897897897'];
    }

    public function testGetAddressDetails()
    {
        $testMethod = new \ReflectionMethod(\Abbott\Hartehanks\Model\HartehankPlaceOrderSync::class, 'getAddressDetails');
        $testMethod->setAccessible(true);
        $data = [
          'entity_id' => 1234,
          'parent_id' => 123,
          'customer_address_id' => 12,
          'quote_address_id' => 12345,
          'region_id' => 12,
          'region' => 'Massachusetts',
          'postcode' => '02351-2111',
          'lastname' => 'lastname "',
          'street' => '"777 Brockton & Ave"',
          'city' => 'Abington',
          'email' => 'test.mail@abbott.com',
          'telephone' => '7897897897',
          'country_id' => 'US',
          'firstname' => 'firstname &',
          'address_type' => 'shipping'
        ];
        $this->assertEquals($this->successOutput, $testMethod->invokeArgs($this->block, [$data]));
        $this->assertNotEquals($this->failureOutput, $testMethod->invokeArgs($this->block, [$data]));
    }

    public function testGetRetryStatus()
    {
        $testMethod = new \ReflectionMethod(\Abbott\Hartehanks\Model\HartehankPlaceOrderSync::class, 'getRetryStatus');
        $testMethod->setAccessible(true);
        $this->assertEquals([1,''], $testMethod->invokeArgs($this->block, [0]));
        $this->assertNotEquals([1,'retry'], $testMethod->invokeArgs($this->block, [0]));
        $this->assertEquals([2,'retry1'], $testMethod->invokeArgs($this->block, [1]));
        $this->assertNotEquals([2,''], $testMethod->invokeArgs($this->block, [1]));
        $this->assertEquals([3,'retry2'], $testMethod->invokeArgs($this->block, [2]));
        $this->assertNotEquals([3,''], $testMethod->invokeArgs($this->block, [2]));
        $this->assertEquals([4,'retry3'], $testMethod->invokeArgs($this->block, [3]));
        $this->assertNotEquals([4,''], $testMethod->invokeArgs($this->block, [3]));
    }
}
