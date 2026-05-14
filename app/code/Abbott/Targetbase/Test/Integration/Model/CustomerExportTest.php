<?php
namespace Abbott\Targetbase\Test\Integration\Model;

use \Abbott\Targetbase\Model\Exportdata;
use \Magento\TestFramework\Helper\Bootstrap;
use \PHPUnit\Framework\TestCase;

class CustomerExportTest extends TestCase
{
   
    public $objectManager;
    public $exportdata;
    /**
     * Build Object
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->exportdata = $this->objectManager->create(Exportdata::class);
    }

    /**
     * Destroy Object
     */
    protected function tearDown()
    {
        $this->objectManager = null;
        $this->exportdata = null;
    }
    
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testExecute()
    {
        $fromDate = "2020-08-01";
        $toDate = "2020-08-15";
        $fileName = $this->exportdata->exportCustomerDataWithDate($fromDate, $toDate);
        $this->assertFileExists($this->exportdata->getCustomerFilePathConsole($fileName . ".pgp"));
    }
}
