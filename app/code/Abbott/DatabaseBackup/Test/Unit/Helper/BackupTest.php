<?php

declare(strict_types=1);

namespace Abbott\DatabaseBackup\Test\Unit\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem\Driver\File;
use Abbott\DatabaseBackup\Helper\Backup;
use Aws\S3\S3Client;
use Aws\Result;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Aws\Waiter;

class BackupTest extends \PHPUnit\Framework\TestCase
{
    const AWS_SYNCED_FILE = "database_backup/status/last_synced_file";
    const SYNCED_FILE_SIZE = "database_backup/status/file_size";
    const SYNCED_DATE_TIME = "database_backup/status/synced_date";

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject  */
    protected $storeManager;

    /** @var File|\PHPUnit\Framework\MockObject\MockObject  */
    protected $fileDriver;

    /** @var object */
    protected $backupHelper;

    /** @var S3Client|\PHPUnit\Framework\MockObject\MockObject  */
    protected $s3Client;

    /** @var WriterInterface|\PHPUnit\Framework\MockObject\MockObject  */
    protected $writeConfig;

    /** @var string */
    protected $fileName;

    protected $filePath;

    /** @var Waiter|\PHPUnit\Framework\MockObject\MockObject  */
    protected $waiter;
    /**
     * Is called before running a test
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->fileDriver = $this->createMock(File::class);
        $this->writeConfig = $this->createMock(WriterInterface::class);
        $this->waiter = $this->createMock(Waiter::class);
        $this->filePath = dirname(dirname(__DIR__)).'/Unit/_files/dbbackup/dump-1604652597.sql.gz';
        $this->fileName = "dump-1604652597.sql.gz";

        $this->backupHelper = $this->objectManager->getObject(
            Backup::class,
            [
             'storeManager' => $this->storeManager,
             'fileDriver' => $this->fileDriver,
             'writeConfig' => $this->writeConfig
            ]
        );
    }

    /**
     * Is called after running a test
     */
    protected function tearDown()
    {
        unset($this->objectManager);
    }

    /**
     *  Function to test sendDataToS3 function from Backup Helper
     */
    public function testSendDataToS3()
    {
        $bucketName= 'test';
        $putObject= [
            'Bucket' => $bucketName,
            'Key' =>  $this->fileName,
            'SourceFile' => $this->filePath,
            'StorageClass' => 'REDUCED_REDUNDANCY'
        ];

        $result = $this->createMock(Result::class);

        $this->s3Client = $this->createPartialMock(S3Client::class, ['putObject','waitUntil']);
        $this->backupHelper->s3Client = $this->s3Client;

            $this->fileDriver->expects($this->once())
            ->method('isExists')
            ->willReturn(true);

        $this->s3Client->expects($this->once())
            ->method('putObject')
            ->with($putObject)
            ->willReturn($result);

        $this->s3Client->expects($this->once())
            ->method('waitUntil')
            ->willReturn($this->waiter);

        $this->assertEquals(true, $this->backupHelper->sendDataToS3($this->filePath, $this->fileName, $bucketName));
    }

    public function testSaveLastDumpData()
    {
        $this->fileDriver->expects($this->once())
            ->method('isExists')
            ->willReturn(true);

        $this->writeConfig->expects($this->at(0))
            ->method('save')
            ->with(self::AWS_SYNCED_FILE, $this->fileName, ScopeInterface::SCOPE_STORE, 1)
            ->willReturn(true);

        $fileSizeInMB = round(filesize($this->filePath) / 1024 / 1024, 1);

        $this->writeConfig->expects($this->at(1))
            ->method('save')
            ->with(self::SYNCED_FILE_SIZE, $fileSizeInMB, ScopeInterface::SCOPE_STORE, 1)
            ->willReturn(true);

        $this->writeConfig->expects($this->at(2))
            ->method('save')
            ->with(self::SYNCED_DATE_TIME, date('Y-m-d H:i:s'), ScopeInterface::SCOPE_STORE, 1)
            ->willReturn(true);

        $store = $this->createMock(StoreInterface::class);

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        $store->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->assertTrue($this->backupHelper->saveLastDumpData($this->filePath, $this->fileName));
    }
}
