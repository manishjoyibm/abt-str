<?php

namespace Abbott\Webhook\Test\Unit;

use Abbott\Webhook\Model\Method\Logger;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

class AttributeSaveAfterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    public $objectManager;

    /**
     * @var (Logger & MockObject)
     */
    public $webhookLoggerMock;
    public $curlHelperMock;
    public $attributeSaveAfterMock;

    /**
     * SetUp
     *
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->webhookLoggerMock = $this->createMock(Logger::class);
        $this->curlHelperMock = $this->createMock(\Abbott\Webhook\Helper\CurlHelper::class);
        $this->attributeSaveAfterMock = $this->objectManager->getObject(
            \Abbott\Webhook\Observer\AttributeSaveAfter::class,
            [
              'helper' => $this->curlHelperMock,
              'webhooklog' => $this->webhookLoggerMock
            ]
        );
    }

    /**
     * TestExecute
     *
     * @return void
     */
    public function testExecute()
    {
        $attributeMock = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $attributeMock->expects($this->once())->method('getAttributeCode')->willReturn('flavors');
        $eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getAttribute']);
        $eventMock->expects($this->any())->method('getAttribute')->willReturn($attributeMock);
        $observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $observerMock->expects($this->any())->method('getEvent')->willReturn($eventMock);
        $this->curlHelperMock->expects($this->once())->method('enabled')->willReturn(true);
        $this->curlHelperMock->expects($this->once())->method('getAttributeCodes')->willReturn("flavors,size");
        $this->curlHelperMock->expects($this->once())
            ->method('getFlavorSizeUrl')
            ->willReturn("https://aem.abcstore.com");
        $this->curlHelperMock->expects($this->once())
            ->method('postData')
            ->with($this->anything())
            ->willReturn("success");
        $this->attributeSaveAfterMock->execute($observerMock);
    }
}
