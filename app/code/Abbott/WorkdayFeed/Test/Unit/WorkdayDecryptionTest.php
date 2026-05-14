<?php

namespace Abbott\WorkdayFeed\Test\Unit;

use Abbott\WorkdayFeed\Helper\InboundFeedHelper;
use Abbott\WorkdayFeed\Model\WorkdayDecryption;

class WorkdayDecryptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    public $objectManager;
    public $shellMock;
    public $directoryMock;
    /**
     * @var (\Magento\Framework\Encryption\EncryptorInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $encryptorMock;
    public $fileMock;
    public $ioMock;
    public $helperMock;
    public $wdDecryptor;
    public $block;
    /**
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->shellMock = $this->createMock(\Magento\Framework\Shell::class);
        $this->directoryMock = $this->createMock(\Magento\Framework\App\Filesystem\DirectoryList::class);
        $this->encryptorMock = $this->createMock(\Magento\Framework\Encryption\EncryptorInterface::class);
        $this->fileMock = $this->createMock(\Magento\Framework\Filesystem\Driver\File::class);
        $this->ioMock = $this->createMock(\Magento\Framework\Filesystem\Io\File::class);
        $this->helperMock = $this->createMock(InboundFeedHelper::class);
        $this->wdDecryptor = $this->objectManager->getObject(
            WorkdayDecryption::class,
            [
                'shell' => $this->shellMock,
                'directory_list' => $this->directoryMock,
                'encryptorInterface' => $this->encryptorMock,
                'file' => $this->fileMock,
                'io' => $this->ioMock,
                'helper' => $this->helperMock
            ]
        );
        $this->block = $this->getMockBuilder(WorkdayDecryption::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return void
     */
    public function testGetPassphrase()
    {
        $testMethod = new \ReflectionMethod(WorkdayDecryption::class, 'getPassphrase');
        $testMethod->setAccessible(true);
        $helperMock = $this->getMockBuilder(InboundFeedHelper::class)
            ->disableOriginalConstructor()->setMethods(["getAbbottPassphrase"])->getMock();
        $helperMock->expects($this->any())->method("getAbbottPassphrase")->will($this->returnValue("Abc:+4523"));
        $this->assertEquals("Abc:+4523", $testMethod->invokeArgs($this->block, [0,$helperMock]));
    }

    /**
     * @return void
     */
    public function testGetCompanyPvtKey()
    {
        $testMethod = new \ReflectionMethod(WorkdayDecryption::class, 'getCompanyPvtKey');
        $testMethod->setAccessible(true);
        $helperMock = $this->getMockBuilder(InboundFeedHelper::class)
            ->disableOriginalConstructor()->setMethods(["getAbbottPvtKey"])
            ->getMock();
        $helperMock->expects($this->any())
            ->method("getAbbottPvtKey")
            ->will($this->returnValue("dqefhgkfhgkwelj94ry973"));
        $this->assertEquals("dqefhgkfhgkwelj94ry973", $testMethod->invokeArgs($this->block, [0,$helperMock]));
    }

    /**
     * @return void
     */
    public function testGetKeyFileName()
    {
        $testMethod = new \ReflectionMethod(WorkdayDecryption::class, 'getKeyFileName');
        $testMethod->setAccessible(true);
        $this->assertEquals("workday_abbott_import-private.key", $testMethod->invokeArgs($this->block, [0]));
    }

    /**
     * @return void
     */
    public function testDecryptWorkdayFilePositive()
    {
        $pvtkey = "hello";
        $passphrase = "abc1234";
        $keyfilepath = "/var/Abbott/WorkdayFeed/workday_abbott_import-private.key";
        $workdayfilepath = "/var/Abbott/WorkdayFeed/abbott-store.txt.gpg";
        $targetFilePath = "/var/Abbott/WorkdayFeed/abbott-store.txt";
        $command1 = 'gpg --batch --yes --allow-secret-key-import --import ' . $keyfilepath;
        $command2 = ($passphrase)? "gpg --pinentry-mode=loopback --output " .
            $targetFilePath . " --passphrase ".$passphrase." --decrypt ".
            $workdayfilepath: "gpg --pinentry-mode=loopback --output " .
            $targetFilePath . " --decrypt " . $workdayfilepath;
        $this->helperMock->expects($this->once())
          ->method('getAbbottPvtKey')
          ->willReturn($pvtkey);
        $this->directoryMock->expects($this->any())
          ->method('getPath')
          ->with('var')
          ->willReturn('/var');
        $this->fileMock->expects($this->once())
          ->method('fileOpen')
          ->with($keyfilepath, "w")
          ->willReturn("filepath");
        $this->fileMock->expects($this->once())
          ->method('fileWrite')
          ->with("filepath", $pvtkey)
          ->willReturnSelf();
        $this->fileMock->expects($this->once())
          ->method('fileClose')
          ->with("filepath")
          ->willReturnSelf();
        $this->helperMock->expects($this->once())
          ->method('getAbbottPassphrase')
          ->willReturn($passphrase);
        $this->shellMock->expects($this->any())
          ->method('execute')->with($this->logicalOr($command1, $command2))
          ->willReturnSelf();
        $this->ioMock->expects($this->once())
          ->method('getPathInfo')
          ->with($workdayfilepath)
          ->willReturn(['filename' => "abbott-store.txt"]);
        $this->fileMock->expects($this->any())
          ->method('isExists')
          ->with($this->logicalOr($workdayfilepath, $targetFilePath))
          ->willReturn(true);
        $inboundMock = $this->createMock(\Abbott\WorkdayFeed\Model\InboundFeed::class);
        $inboundMock->expects($this->once())->method('setFileName')->with("abbott-store.txt")->willReturnSelf();
        $inboundMock->expects($this->any())->method('setMessage')->with($this->anything())->willReturnSelf();
        $inboundMock->expects($this->any())->method('save')->willReturnSelf();
        $result = $this->wdDecryptor->decryptWorkdayFile($inboundMock, $workdayfilepath, 0);
        $this->assertTrue($result);
    }

    /**
     * @return void
     */
    public function testDecryptWorkdayFileNegative()
    {
        $workdayfilepath = "/var/Abbott/WorkdayFeed/abbott-store.txt.gpg";
        $this->helperMock->expects($this->once())
          ->method('getAbbottPvtKey')
          ->willReturn(null);
        $inboundMock = $this->createMock(\Abbott\WorkdayFeed\Model\InboundFeed::class);
        $inboundMock->expects($this->any())->method('setMessage')->with($this->anything())->willReturnSelf();
        $inboundMock->expects($this->any())->method('save')->willReturnSelf();
        $result = $this->wdDecryptor->decryptWorkdayFile($inboundMock, $workdayfilepath, 0);
        $this->assertFalse($result);
    }
}
