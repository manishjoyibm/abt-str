<?php

namespace Abbott\Webhook\Test\Unit;

use Abbott\Webhook\Model\Method\Logger;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

class PlanSaveAfterTest extends \PHPUnit\Framework\TestCase
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
    public $planSaveAfterMock;

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
        $this->planSaveAfterMock = $this->objectManager->getObject(
            \Abbott\Webhook\Model\PlanSaveAfter::class,
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
        $this->curlHelperMock->expects($this->once())->method('enabled')->willReturn(true);
        $this->curlHelperMock->expects($this->once())
            ->method('getFlavorSizeUrl')
            ->willReturn("https://aem.abcstore.com");
        $this->curlHelperMock->expects($this->once())
            ->method('postData')
            ->with($this->anything())
            ->willReturn("success");
        $this->planSaveAfterMock->execute();
    }
}
