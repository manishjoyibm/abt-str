<?php

namespace Abbott\DatabaseBackup\Model;

use Abbott\DatabaseBackup\Model\DBBackup;
use Abbott\DatabaseBackup\Helper\Backup;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Shell;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\DeploymentConfig;
use Abbott\DatabaseBackup\Model\DatabaseConnection;

class DBBackupTest extends TestCase
{
    public $dbbackup;
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /** @var object */
    protected $backupHelper;

    /** @var WriterInterface|\PHPUnit\Framework\MockObject\MockObject  */
    protected $writeConfig;

    /**
     * @var \Abbott\DatabaseBackup\Model\DatabaseConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $databaseConnection;
    /**
     * @var DeploymentConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    private $deploymentConfig;
    /**
     * @var Shell|\PHPUnit\Framework\MockObject\MockObject
     */
    private $shell;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->backupHelper = $this->createMock(Backup::class);
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->databaseConnection = $this->createMock(DatabaseConnection::class);
        $this->shell = $this->createMock(Shell::class);

        $this->dbbackup = $this->objectManager->getObject(
            DBBackup::class,
            [
                'backup' => $this->backupHelper,
                'deploymentConfig' => $this->deploymentConfig,
                'databaseConnection' => $this->databaseConnection,
                'shell'=> $this->shell
            ]
        );
    }

    public function testPrepareSql()
    {
        $hostName = 'test';
        $dataBaseName = 'magento';
        $userName = 'user';
        $password = 'password';
        $portString = '3306';

        $command = "mysqldump -h " . escapeshellarg($hostName) .
        " -u " . escapeshellarg($userName) . " -p" . escapeshellarg($password) ." -P". escapeshellarg($portString)." ".
        escapeshellarg($dataBaseName) . " --single-transaction --no-autocommit --quick";
        $this->assertEquals(
            $command,
            $this->dbbackup->prepareSql($hostName, $dataBaseName, $userName, $password, $portString)
        );
    }
}
