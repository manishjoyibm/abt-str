<?php

namespace Abbott\Hartehanks\Test\Unit;

class HartehankSyncTest extends \PHPUnit\Framework\TestCase
{
    public $block;
    public $failureOutput;
    public $successOutput;
    const HH_INVENTORY_SERVICE = 'HH Inventory Service';

    const TEST_TIME = '2019-12-17 20:13:04';

    public function setUp()
    {
        $this->block = $this->getMockBuilder(\Abbott\Hartehanks\Model\HartehankSync::class)->disableOriginalConstructor()->getMock();
        $this->block->failCounter = 0;
        $this->block->succesCounter = 1;
        $this->failureOutput = ['status_fail' => 'Failed','creation_time' => self::TEST_TIME];
        $this->successOutput = [
            'file_name' => self::HH_INVENTORY_SERVICE,
            'creation_time' => self::TEST_TIME,
            'total_records' => 1,
            'added' => 1,
            'failed' => 0,
        ];
    }

    public function testEmailTemplateData()
    {
        $testMethod = new \ReflectionMethod(\Abbott\Hartehanks\Model\HartehankSync::class, 'emailTemplateData');
        $testMethod->setAccessible(true);
        $this->assertEquals($this->failureOutput, $testMethod->invokeArgs($this->block, ['failure_email_template',self::HH_INVENTORY_SERVICE,self::TEST_TIME,1]));
        $this->assertNotEquals($this->successOutput, $testMethod->invokeArgs($this->block, ['failure_email_template',self::HH_INVENTORY_SERVICE,self::TEST_TIME,1]));
        $this->assertEquals($this->successOutput, $testMethod->invokeArgs($this->block, ['hartehanks/hartehank_email_template/email_template',self::HH_INVENTORY_SERVICE,self::TEST_TIME,1]));
        $this->assertNotEquals($this->failureOutput, $testMethod->invokeArgs($this->block, ['hartehanks/hartehank_email_template/email_template',self::HH_INVENTORY_SERVICE,self::TEST_TIME,1]));
    }
}
