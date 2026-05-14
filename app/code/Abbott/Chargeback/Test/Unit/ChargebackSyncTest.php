<?php

namespace Abbott\Chargeback\Test\Unit;

class ChargebackSyncTest extends \PHPUnit\Framework\TestCase
{
    public $block;
    public $successOutput;
    const FILE_NAME = 'chargeback.csv';

    const TEST_TIME = '2019-12-17 20:13:04';

    /**
     * @return void
     */
    public function setUp()
    {
        $this->block = $this->getMockBuilder(\Abbott\Chargeback\Model\ChargebackSync::class)->disableOriginalConstructor()->getMock();
        $this->block->failureCounter = 0;
        $this->block->successCounter = 1;
        $this->successOutput = [
            'file_name' => self::FILE_NAME,
            'creation_time' => self::TEST_TIME,
            'total_records' => 1,
            'success' => 1,
            'failed' => 0,
        ];
    }
    
    /**
     * @return void
     */
    public function testGetFailedTemplateData()
    {
        $testMethod = new \ReflectionMethod(\Abbott\Chargeback\Model\ChargebackSync::class, 'getFailedTemplateData');
        $testMethod->setAccessible(true);
        $inboundFeed = $this->getMockBuilder(\Abbott\WorkdayFeed\Model\InboundFeed::class)->disableOriginalConstructor()->setMethods(['getCreatedAt','getFileName'])->getMock();
        $inboundFeed->method('getCreatedAt')->will($this->returnValue(self::TEST_TIME));
        $inboundFeed->method('getFileName')->will($this->returnValue(self::FILE_NAME));
        $this->assertEquals($this->successOutput, $testMethod->invokeArgs($this->block, [$inboundFeed,1]));
        $this->block->failureCounter = 1;
        $this->block->successCounter = 0;
        $this->assertNotEquals($this->successOutput, $testMethod->invokeArgs($this->block, [$inboundFeed,1]));
    }
}
