<?php

namespace Abbott\WorkdayFeed\Test\Unit;

use Abbott\WorkdayFeed\Model\WorkdaySync;

class WorkdaySyncTest extends \PHPUnit\Framework\TestCase
{
    public $block;
    public $successHeader;
    public $failureHeaders;
    public $records;
    public function setUp()
    {
        $this->block = $this->getMockBuilder(WorkdaySync::class)
            ->disableOriginalConstructor()->getMock();
        $this->successHeader = 'Record Status|Status|UPI|Last Name|First Name|Middle Initial|Company|Email-ID';
        $this->failureHeaders = [
                          'Record Status|Email-ID|Status|UPI|Last Name|First Name|Middle Initial|Company',
                          'Record Status|Email-ID|Status|UPI|Last Name|First Name|Middle Initial|Company|Ra',
                        ];
        $this->records = [ preg_replace("/[\r\n]/", "", explode("|", 'A|A|10226217|AHLBERG|WILLIAM||ABBOTT|')),
                           preg_replace("/[\r\n]/", "", explode("|", 'M|A|10362183|Gause|Kenneth|W|ABBOTT|'))
                        ];
    }

    public function testHeader()
    {
        $testMethod = new \ReflectionMethod(WorkdaySync::class, 'fileValidator');
        $testMethod->setAccessible(true);
        $this->assertTrue($testMethod->invokeArgs($this->block, [$this->successHeader]));
        foreach ($this->failureHeaders as $header) {
            $this->assertFalse($testMethod->invokeArgs($this->block, [$header]));
        }
    }
    public function testGenEmail()
    {
        $testMethod = new \ReflectionMethod(WorkdaySync::class, 'genEmail');
        $testMethod->setAccessible(true);
        $this->assertEquals(
            'williamahlberg@noreply.abbott.com',
            $testMethod->invokeArgs($this->block, [$this->records[0]])
        );
        $this->assertNotEquals(
            'KENNETHGAUSE@noreply.abbott.com',
            $testMethod->invokeArgs($this->block, [$this->records[1]])
        );
    }
}
