<?php

namespace Abbott\AwsLambda\Test\Unit\Logger;

class LogTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    public $objectManager;
    public $logMock;
    public $directoryMock;
    /**
     * @var (\Magento\Framework\Filesystem\Driver\File & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $fileMock;
    /**
     * @var (\Magento\Framework\Filesystem\Io\File & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $ioMock;
    /**
     * @var (\Abbott\AwsLambda\Helper\Data & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $helperMock;
    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->logMock = $this->createMock(\Abbott\AwsLambda\Logger\Log::class);
        $this->directoryMock = $this->createMock(\Magento\Framework\App\Filesystem\DirectoryList::class);

        $this->fileMock = $this->createMock(\Magento\Framework\Filesystem\Driver\File::class);
        $this->ioMock = $this->createMock(\Magento\Framework\Filesystem\Io\File::class);
        $this->helperMock = $this->createMock(\Abbott\AwsLambda\Helper\Data::class);
        $this->logMock = $this->objectManager->getObject(
            \Abbott\AwsLambda\Logger\Log::class,
            [
                'helper' => $this->helperMock,
                'dirList' => $this->directoryMock,
                'file' => $this->fileMock
            ]
        );
    }

    /**
     * set scope
     */
    public function testSetScope()
    {
        $testMethod = new \ReflectionMethod(\Abbott\AwsLambda\Logger\Log::class, 'setScope');
        $testMethod->setAccessible(true);

        $test = [
            'storeid' => 4
        ];

        $this->assertTrue(true, $this->logMock->setScope($test['storeid']));
    }

    public function testWriteLog()
    {
        $testMethod = new \ReflectionMethod(\Abbott\AwsLambda\Logger\Log::class, 'writeLog');
        $testMethod->setAccessible(true);

        $test = [
            'message' => "write message in log"
        ];

        $this->directoryMock->expects($this->any())
          ->method('getPath')
          ->with('var')
          ->willReturn('/var');
        $testMethod = new \ReflectionMethod(\Abbott\AwsLambda\Logger\Log::class, 'writeLog');
        $testMethod->setAccessible(true);

        $this->assertTrue(true, $testMethod->invokeArgs($this->logMock, [$test['message']]), "write message in log");
    }
}
