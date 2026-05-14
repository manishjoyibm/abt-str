<?php

namespace Abbott\GigyaIM\Test\Unit\Block\Adminhtml\Customer\Edit\Tab\View;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Abbott\GigyaIM\Block\Adminhtml\Customer\Edit\Tab\View\GigyaUID;
use Magento\Framework\App\Area;

class GigyaUIDTest extends \PHPUnit\Framework\TestCase
{
    public $objectManagerHelper;
    public $helperMock;
    /**
     * @var (\Magento\Backend\Block\Template\Context & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $contextMock;
    public $_customer;
    public $gigyaUIDMock;
    public $obj;
    public function setUp() : void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->helperMock  = $this->getMockBuilder(\Abbott\GigyaIM\Helper\Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['isGigyaEnabledForWebsite'])
            ->getMock();
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\Block\Template\Context::class)
            ->disableOriginalConstructor()->getMock();

        $this->_customer = $this->createMock(\Magento\Customer\Model\Data\Customer::class);

        $this->gigyaUIDMock = $this->getMockBuilder(GigyaUID::class)
            ->setMethods(['__construct', 'getCustomerFromParentBlock'])
            ->setConstructorArgs([$this->contextMock, $this->helperMock])
            ->getMock();
    }

    public function testgetGUIDFromParentBlock3(): void
    {
        $className = GigyaUID::class;
        $arguments = $this->objectManagerHelper->getConstructArguments($className);
        $this->obj = $this->objectManagerHelper->getObject($className, $arguments);
        $attr = $this->getMockBuilder(\Magento\Framework\Api\AttributeValue::class)
            ->setMethods(['getValue'])->getMock();
        $attr->method('getValue')->will($this->returnValue(1));
        $this->_customer->method('getCustomAttribute')->with('gigya_uid')->willReturn($attr);
        $this->obj->getGUIDFromParentBlock();
    }

    public function testIsGigyaEnabledForWebsiteCase1() : void
    {
        $testMethod = new \ReflectionMethod(GigyaUID::class, 'isGigyaEnabledForWebsite');
        $testMethod->setAccessible(true);

        $test = [
            'website_id' => 4,
            'is_gigya_enabled_for_website' => 1,
            'expected_is_gigya_enabled_for_website' => 1
        ];

        $this->helperMock->method('isGigyaEnabledForWebsite')
            ->will($this->returnValue($test['is_gigya_enabled_for_website']));
        $this->_customer->method('getWebsiteId')->will($this->returnValue($test['website_id']));

        $this->gigyaUIDMock->method('getCustomerFromParentBlock')->will($this->returnValue($this->_customer));
        
        $this->assertEquals(
            $test['expected_is_gigya_enabled_for_website'],
            $testMethod->invokeArgs($this->gigyaUIDMock, []),
            "Check if the gigya is enabled for the customer website"
        );
    }
    
    public function testIsGigyaEnabledForWebsiteCase2() : void
    {
        $testMethod = new \ReflectionMethod(\Abbott\GigyaIM\Block\Adminhtml\Customer\Edit\Tab\View\GigyaUID::class, 'isGigyaEnabledForWebsite');
        $testMethod->setAccessible(true);

        $test = [
            'website_id' => 1,
            'is_gigya_enabled_for_website' => 0,
            'expected_is_gigya_enabled_for_website' => 0
        ];

        $this->helperMock->method('isGigyaEnabledForWebsite')->will($this->returnValue($test['is_gigya_enabled_for_website']));
        $this->_customer->method('getWebsiteId')->will($this->returnValue($test['website_id']));
        $this->gigyaUIDMock->method('getCustomerFromParentBlock')->will($this->returnValue($this->_customer));

        $this->assertEquals($test['expected_is_gigya_enabled_for_website'], $testMethod->invokeArgs($this->gigyaUIDMock, []), "Check if the gigya is disabled for the customer website");
    }

    public function testIsGigyaEnabledForWebsiteCase3() : void
    {
        $testMethod = new \ReflectionMethod(\Abbott\GigyaIM\Block\Adminhtml\Customer\Edit\Tab\View\GigyaUID::class, 'isGigyaEnabledForWebsite');
        $testMethod->setAccessible(true);

        $test = [
            'website_id' => 1,
            'expected_is_gigya_enabled_for_website' => 0
        ];

        $this->gigyaUIDMock->method('getCustomerFromParentBlock')->will($this->returnValue(false));

        $this->assertEquals($test['expected_is_gigya_enabled_for_website'], $testMethod->invokeArgs($this->gigyaUIDMock, []), "Check if the gigya is disabled for the invalid customer");
    }

    public function testgetGUIDFromParentBlock() : void
    {
        $this->_customer->method('getCustomAttribute')->with('gigya_uid')->will($this->returnValue(null));

        $this->gigyaUIDMock->method('getCustomerFromParentBlock')->will($this->returnValue($this->_customer));

        $this->assertFalse(
            $this->gigyaUIDMock->getGUIDFromParentBlock()
        );
    }

    public function testgetGUIDFromParentBlock2(): void
    {
        $attr = $this->getMockBuilder(\Magento\Framework\Api\AttributeValue::class)
            ->setMethods(['getValue'])->getMock();
        $attr->method('getValue')->will($this->returnValue(1));
        $this->_customer->method('getCustomAttribute')->with('gigya_uid')->willReturn($attr);
        $this->gigyaUIDMock->method('getCustomerFromParentBlock')->will($this->returnValue($this->_customer));
        $this->assertEquals(1, $this->gigyaUIDMock->getGUIDFromParentBlock());
    }

    /**
     * @covers Abbott\GigyaIM\Block\Adminhtml\Customer\Edit\Tab\View\GigyaUID::getCustomerFromParentBlock
     */
    public function testgetCustomerFromParentBlock(): void
    {
        $testMethod = new \ReflectionMethod(\Abbott\GigyaIM\Block\Adminhtml\Customer\Edit\Tab\View\GigyaUID::class, 'isGigyaEnabledForWebsite');
        $testMethod->setAccessible(true);

        $test = [
            'website_id' => 1,
            'expected_is_gigya_enabled_for_website' => 0
        ];
        $parent = $this->getMockBuilder(\Magento\Backend\Block\Template::class)
            ->disableOriginalConstructor()->getMock();
        $parent->method('getParentBlock')->willReturn(false);

        $this->assertEquals(false, $testMethod->invokeArgs($this->gigyaUIDMock, []), false);
    }
}
