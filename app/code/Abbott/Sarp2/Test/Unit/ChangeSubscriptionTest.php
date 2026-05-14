<?php

namespace Abbott\Sarp2\Test\Unit;

class ChangeSubscriptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    public $objectManager;
    public $requestMock;
    public $storeManagerMock;
    /**
     * @var (\Magento\Framework\App\Config\ScopeConfigInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $scopeConfigMock;
    public $profileMock;
    public $transportBuilderMock;
    public $helperMock;
    public $changeSubscriptionMock;
    public function setUp()
    {
      $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
      $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
      $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
      $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
      $this->profileMock = $this->createMock(\Aheadworks\Sarp2\Model\Profile::class);
      $this->transportBuilderMock = $this->createMock(\Magento\Framework\Mail\Template\TransportBuilder::class);
      $this->helperMock = $this->createMock(\Abbott\Sarp2\Helper\Data::class);
      $this->changeSubscriptionMock = $this->objectManager->getObject(
          \Abbott\Sarp2\Helper\ChangeSubscription::class,
          [
              'request' => $this->requestMock,
              'storeManager' => $this->storeManagerMock,
              'profile' => $this->profileMock,
              'scopeConfig' => $this->scopeConfigMock,
              'transportBuilder' => $this->transportBuilderMock,
              'helper' => $this->helperMock
          ]
      );
    }

    public function testUpdateSubscriptionNotificationAdminhtml()
    {
        $profileId = 1;
        $profMock = $this->createMock(\Aheadworks\Sarp2\Model\Profile::class);
        $profMock->expects($this->once())->method('getIncrementId')->willReturn("100000001");
        $profMock->expects($this->once())->method('getCreatedAt')->willReturn("2020-06-21 15:24:11");
        $profMock->expects($this->once())->method('getCustomerFullname')->willReturn("John Doe");
        $profMock->expects($this->once())->method('getCustomerEmail')->willReturn("testemail@test.com");
        $profMock->expects($this->once())->method('getStoreId')->willReturn(1);
        $this->requestMock->expects($this->once())->method('getParam')->with('profile_id')->willReturn($profileId);
        $this->profileMock->expects($this->once())->method('load')->with($profileId)->willReturn($profMock);
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $storeMock->expects($this->once())->method('getUrl')->willReturn("www.abc.com");
        $storeMock->expects($this->once())->method('getFrontendName')->willReturn("AbcStore");
        $this->storeManagerMock->expects($this->once())->method('getStore')->with(1)->willReturn($storeMock);
        $this->helperMock->expects($this->once())->method('getStorePhone')->willReturn("12345678");
        $this->helperMock->expects($this->once())->method('getStoreSenderName')->with(1)->willReturn("QA ABC");
        $this->helperMock->expects($this->once())->method('getStoreSenderEmail')->with(1)->willReturn("qaabc@noreply.com");
        $transportMock = $this->getMockBuilder(\Magento\Framework\Mail\TransportInterface::class)->setMethods(['sendMessage'])->getMockForAbstractClass();
        $transportMock->expects($this->once())->method('sendMessage');
        $this->transportBuilderMock->expects($this->once())->method('setTemplateIdentifier')->with($this->anything())->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())->method('setTemplateOptions')->with($this->anything())->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())->method('setTemplateVars')->with($this->anything())->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())->method('setFrom')->with($this->anything())->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())->method('addTo')->with($this->anything())->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())->method('getTransport')->willReturn($transportMock);
        $this->changeSubscriptionMock->updateSubscriptionNotificationAdminhtml();

    }
}
