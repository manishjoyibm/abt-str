<?php

namespace Abbott\GigyaIM\Test\Unit\Model\ResourceModel;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Abbott\GigyaIM\Model\ResourceModel\SsmCart;

class SsmCartTest extends \PHPUnit\Framework\TestCase
{
    public $objectManagerHelper;
    public $model;
    public function testgetByEmailAndWebsite()
    {
        $email = "abc@abc.com";
        $website = 1;
        $connection = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
            [],
            '',
            false
        );
        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $connection->expects($this->exactly(1))
            ->method('select')
            ->willReturn($select);
        $select->expects($this->exactly(1))
            ->method('from')
            ->willReturnSelf();
        $select->expects($this->exactly(2))
            ->method('where')
            ->willReturnSelf();
        
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $className = SsmCart::class;
        $arguments = $this->objectManagerHelper->getConstructArguments($className);

        $context = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $resources = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $resources->expects($this->any())->method('getConnection')->willReturn($connection);
        $context->expects($this->once())->method('getResources')->willReturn($resources);
        $arguments['context'] = $context;

        $this->model = (new ObjectManagerHelper($this))->getObject(
            $className,
            $arguments
        );

        $return = $this->model->getByEmailAndWebsite($email, $website);
        $this->assertEquals(null, $return);
    }
}
