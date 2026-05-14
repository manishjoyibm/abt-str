<?php

namespace Abbott\Chargeback\Test\Unit;

class SaveTest extends \PHPUnit\Framework\TestCase
{
    public $block;
    public $failureOutput;
    public $successOutput;
    /**
     * @return void
     */
    public function setUp()
    {
        $this->block = $this->getMockBuilder(\Abbott\Chargeback\Controller\Adminhtml\Chargeback\Save::class)->disableOriginalConstructor()->getMock();
        $this->block->failCounter = 0;
        $this->block->succesCounter = 1;
        $this->failureOutput = ['type' => 'Failed.csv'];
        $this->successOutput = ['file_name' => 'TestFileName.csv'];
    }
    
    /**
     * @return void
     */
    public function testFilterFileData()
    {
        $testMethod = new \ReflectionMethod(\Abbott\Chargeback\Controller\Adminhtml\Chargeback\Save::class, 'filterFileData');
        $testMethod->setAccessible(true);
        $data = ['file_name' => [['name' => 'TestFileName.csv','type' => 'text/csv']]];
        $this->assertEquals($this->successOutput, $testMethod->invokeArgs($this->block, [$data]));
        $this->assertNotEquals($this->failureOutput, $testMethod->invokeArgs($this->block, [$data]));
    }
}
