<?php

namespace Abbott\GigyaIM\Test\Unit\Plugin;

class CustomerAttributeTest extends \PHPUnit\Framework\TestCase
{
    public $helperMock;
    /**
     * @var (\Abbott\AwsLambda\Logger\Log & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $loggerMock;
    public $sessionMock;
    public $plugin;
    public function setUp() : void
    {
        $this->helperMock  = $this->getMockBuilder(\Abbott\GigyaIM\Helper\Data::class)->disableOriginalConstructor()->setMethods(['isGigyaFieldsEditable', 'isGigyaEnabledForWebsite'])->getMock();

        $this->loggerMock  = $this->getMockBuilder(\Abbott\AwsLambda\Logger\Log::class)->disableOriginalConstructor()->getMock();

        $this->sessionMock = $this->createMock(\Magento\Backend\Model\Session::class);

        $this->plugin = new \Abbott\GigyaIM\Plugin\CustomerAttributePlugin($this->helperMock, $this->loggerMock, $this->sessionMock);
    }
    public function testbeforeGetAttributesMeta()
    {
        $arr['customer_data']['account']['website_id'] = 1;
        $subject = $this->createMock(\Magento\Customer\Model\AttributeMetadataResolver::class);
        $attribute = $this->createMock(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class);
        $attribute->method('getAttributeCode')->will($this->returnValue('gigya_uid'));
        $this->helperMock->method('isGigyaEnabledForWebsite')->willReturn(true);
        $this->helperMock->method('isGigyaFieldsEditable')->willReturn(true);
        $this->sessionMock->method('getData')->willReturn($arr);
        $this->plugin->beforeGetAttributesMeta($subject, $attribute, "sd", true);
        //$this->assertEquals($this->returnBlock, $this->helper->afterSaveRma($this->returnBlock));
    }
}
