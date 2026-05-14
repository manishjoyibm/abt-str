<?php

namespace Abbott\GigyaIM\Test\Unit\Helper;

use Magento\Framework\App\Area;

class DataTest extends \PHPUnit\Framework\TestCase
{
    public $context;
    public $state;
    public $storeManager;
    public $accountHelper;
    public $cookieManagerInterface;
    public $cookieMetadataFactory;
    public $scopeConfig;
    public $helper;
    public function setUp(): void
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = \Abbott\GigyaIM\Helper\Data::class;
        $arguments = $objectManagerHelper->getConstructArguments($className);
        $this->context = $arguments['context'];
        $this->state = $arguments['state'];
        $this->storeManager = $arguments['storeManager'];
        $this->accountHelper = $arguments['accountHelper'];
        $this->cookieManagerInterface = $arguments['cookieManagerInterface'];
        $this->cookieMetadataFactory = $arguments['cookieMetadataFactory'];
        $this->scopeConfig = $this->context->getScopeConfig();
        $this->helper = $objectManagerHelper->getObject($className, $arguments);
    }

    /**
     * @param $websiteId
     * @dataProvider getWebsiteIdProvider
     */
    public function testisGigyaFieldsEditable1($websiteId)
    {
        $test = [
            'website_id' => 1,
            'website_code' => 'base',
            'area_code' => Area::AREA_ADMINHTML,
            'is_gigya_fields_editable' => 1,
            'expected_is_gigya_fields_editable' => 1
        ];
        $this->state->expects($this->any())->method('getAreaCode')->will($this->returnValue(Area::AREA_ADMINHTML));

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $store->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($test['website_code']));

        $this->storeManager->expects($this->any())
            ->method('getWebsite')
            ->with($websiteId)
            ->will($this->returnValue($store));

        $this->scopeConfig->expects($this->at(0))
            ->method('getValue')
            ->with(
                \Abbott\GigyaIM\Helper\Data::IS_GIGYA_FIELDS_EDITABLE,
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                $test['website_code']
            )
            ->will($this->returnValue($test['expected_is_gigya_fields_editable']));

        $this->assertEquals(
            $test['expected_is_gigya_fields_editable'],
            $this->helper->isGigyaFieldsEditable($websiteId)
        );
    }

    public function getWebsiteIdProvider()
    {
        return [
            [1]
        ];
    }

    /**
     * @param $websiteId
     */
    public function testisGigyaFieldsEditable2($websiteId = null)
    {
        $test = [
            'website_id' => 1,
            'website_code' => 'base',
            'area_code' => Area::AREA_ADMINHTML,
            'is_gigya_fields_editable' => 1,
            'expected_is_gigya_fields_editable' => 1,
            'expected_is_gigya_fields_editable_default' => 0
        ];
        $this->state->expects($this->any())->method('getAreaCode')->will($this->returnValue($test['area_code']));

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $store->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($test['website_code']));

        $this->storeManager->expects($this->any())
            ->method('getWebsite')
            ->with($websiteId)
            ->will($this->returnValue($store));

        $this->assertEquals(
            $test['expected_is_gigya_fields_editable_default'],
            $this->helper->isGigyaFieldsEditable($websiteId)
        );
    }

    /**
     * @param $websiteId
     * @dataProvider getWebsiteIdProvider
     */
    public function testisGigyaFieldsEditable3($websiteId)
    {
        $test = [
            'website_id' => 1,
            'website_code' => 'base',
            'area_code' => "frontend",
            'is_gigya_fields_editable' => 1,
            'expected_is_gigya_fields_editable' => 1,
            'expected_is_gigya_fields_editable_default' => 0
        ];
        $this->state->expects($this->any())->method('getAreaCode')->will($this->returnValue($test['area_code']));

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $store->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($test['website_code']));

        $this->storeManager->expects($this->any())
            ->method('getWebsite')
            ->with($websiteId)
            ->will($this->returnValue($store));

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(
                \Abbott\GigyaIM\Helper\Data::IS_GIGYA_FIELDS_EDITABLE,
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
            )
            ->will($this->returnValue($test['expected_is_gigya_fields_editable_default']));

        $this->assertEquals(
            $test['expected_is_gigya_fields_editable_default'],
            $this->helper->isGigyaFieldsEditable($websiteId)
        );
    }

    /**
     * @param $websiteId
     */
    public function testisGigyaFieldsEditableExp($websiteId = null)
    {
        $test = [
            'website_id' => 1,
            'website_code' => 'base',
            'area_code' => Area::AREA_ADMINHTML,
            'is_gigya_fields_editable' => 1,
            'expected_is_gigya_fields_editable' => 1,
            'expected_is_gigya_fields_editable_default' => 0
        ];
        $this->state->expects($this->any())->method('getAreaCode')
        ->will($this->throwException(new \Magento\Framework\Exception\LocalizedException(__("abc"))));

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $store->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($test['website_code']));

        $this->scopeConfig->expects($this->once())
        ->method('getValue')
        ->with(
            \Abbott\GigyaIM\Helper\Data::IS_GIGYA_FIELDS_EDITABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        )
            ->will($this->returnValue($test['expected_is_gigya_fields_editable_default']));

        $this->assertEquals(
            $test['expected_is_gigya_fields_editable_default'],
            $this->helper->isGigyaFieldsEditable($websiteId)
        );
    }

    /**
     * @param $websiteId
     * @dataProvider getWebsiteIdProvider
     */
    public function testisGigyaEnabledForWebsite1($websiteId)
    {
        $test = [
            'website_id' => 1,
            'website_code' => 'base',
            'area_code' => Area::AREA_ADMINHTML,
            'is_gigya_fields_editable' => 1,
            'expected_is_gigya_fields_editable' => 1
        ];
        $this->state->expects($this->any())->method('getAreaCode')->will($this->returnValue(Area::AREA_ADMINHTML));

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $store->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($test['website_code']));

        $this->storeManager->expects($this->any())
            ->method('getWebsite')
            ->with($websiteId)
            ->will($this->returnValue($store));

        $this->scopeConfig->expects($this->at(0))
            ->method('getValue')
            ->with(
                \Abbott\GigyaIM\Helper\Data::IS_GIGYA_ENABLED_WEBSITE,
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                $test['website_code']
            )
            ->will($this->returnValue($test['expected_is_gigya_fields_editable']));

        $this->assertEquals(
            $test['expected_is_gigya_fields_editable'],
            $this->helper->isGigyaEnabledForWebsite($websiteId)
        );
    }

    /**
     * @param $websiteId
     */
    public function testisGigyaEnabledForWebsite2($websiteId = null)
    {
        $test = [
            'website_id' => 1,
            'website_code' => 'base',
            'area_code' => Area::AREA_ADMINHTML,
            'is_gigya_fields_editable' => 1,
            'expected_is_gigya_fields_editable' => 1,
            'expected_is_gigya_fields_editable_default' => 0
        ];
        $this->state->expects($this->any())->method('getAreaCode')->will($this->returnValue($test['area_code']));

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $store->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($test['website_code']));

        $this->storeManager->expects($this->any())
            ->method('getWebsite')
            ->with($websiteId)
            ->will($this->returnValue($store));

        $this->assertEquals(
            $test['expected_is_gigya_fields_editable_default'],
            $this->helper->isGigyaEnabledForWebsite($websiteId)
        );
    }

    /**
     * @param $websiteId
     * @dataProvider getWebsiteIdProvider
     */
    public function testisGigyaEnabledForWebsite3($websiteId)
    {
        $test = [
            'website_id' => 1,
            'website_code' => 'base',
            'area_code' => "frontend",
            'is_gigya_fields_editable' => 1,
            'expected_is_gigya_fields_editable' => 1,
            'expected_is_gigya_fields_editable_default' => 0
        ];
        $this->state->expects($this->any())->method('getAreaCode')->will($this->returnValue($test['area_code']));

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $store->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($test['website_code']));

        $this->storeManager->expects($this->any())
            ->method('getWebsite')
            ->with($websiteId)
            ->will($this->returnValue($store));

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(
                \Abbott\GigyaIM\Helper\Data::IS_GIGYA_ENABLED_WEBSITE,
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
            )
            ->will($this->returnValue($test['expected_is_gigya_fields_editable_default']));

        $this->assertEquals(
            $test['expected_is_gigya_fields_editable_default'],
            $this->helper->isGigyaEnabledForWebsite($websiteId)
        );
    }

    /**
     * @param $websiteId
     */
    public function testisGigyaEnabledForWebsiteExp($websiteId = null)
    {
        $test = [
            'website_id' => 1,
            'website_code' => 'base',
            'area_code' => Area::AREA_ADMINHTML,
            'is_gigya_fields_editable' => 1,
            'expected_is_gigya_fields_editable' => 1,
            'expected_is_gigya_fields_editable_default' => 0
        ];
        $this->state->expects($this->any())->method('getAreaCode')
        ->will($this->throwException(new \Magento\Framework\Exception\LocalizedException(__("abc"))));

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $store->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($test['website_code']));

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(
                \Abbott\GigyaIM\Helper\Data::IS_GIGYA_ENABLED_WEBSITE,
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
            )
            ->will($this->returnValue($test['expected_is_gigya_fields_editable_default']));

        $this->assertEquals(
            $test['expected_is_gigya_fields_editable_default'],
            $this->helper->isGigyaEnabledForWebsite($websiteId)
        );
    }

    /**
     * @param $websiteId
     * @dataProvider setCookieProvider
     */
    public function testsetCookie($key, $value)
    {
        $this->accountHelper->expects($this->once())->method('getCookieRedirect')
        ->will($this->returnValue("abc.com"));
        $pub = $this->getMockBuilder(\Magento\Framework\Stdlib\Cookie\PublicCookieMetadata::class)->disableOriginalConstructor()->getMock();
        $pub->expects($this->once())->method('setPath')->with('/')->willReturn($this);
        $pub->expects($this->once())->method('setDomain')->with('abc.com')->willReturn($this);
        $pub->expects($this->once())->method('setHttpOnly')->with(false)->willReturn($this);
        $pub->expects($this->once())->method('setSecure')->with(true)->willReturn($this);
        $this->cookieMetadataFactory->expects($this->once())->method('createPublicCookieMetadata')->willReturn($pub);
        $this->cookieManagerInterface->expects($this->once())
        ->method('setPublicCookie')
        ->with($key, $value, $pub)
        ->willReturn($this);
        $this->helper->setCookie($key, $value);
    }

    public function setCookieProvider()
    {
        return [
            ['key', '123']
        ];
    }
}
