<?php

namespace Abbott\GigyaIM\Test\Unit\Helper;

use Magento\Framework\App\Area;

class CustomerHelperTest extends \PHPUnit\Framework\TestCase
{
    public $objectManagerHelper;
    public $context;
    /**
     * @var object
     */
    public $filterGroupBuilder;
    /**
     * @var object
     */
    public $searchCriteriaBuilder;
    public $customerRepo;
    public $ssmCartRepo;
    public $getCartForUser;
    public $createEmptyCartForGuest;
    public $storeManager;
    public $ssmCartInterface;
    public $cartManagement;
    public $maskedQuoteIdToQuoteId;
    public $cartRepository;
    /**
     * @var object
     */
    public $sortOrderBuilder;
    /**
     * @var object
     */
    public $filterBuilder;
    public $quote;
    public $helper;
    /**
     * @var \PHPUnit\Framework\MockObject\MockBuilder
     */
    public $ssmInterface;
    const EMAIL = 'abc@ab.com';
    const TEST_EMAIL = 'abc@abc.com';

    public function setUp(): void
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $objectManagerHelper = $this->objectManagerHelper;
        $className = \Abbott\GigyaIM\Helper\CustomerHelper::class;
        $arguments = $objectManagerHelper->getConstructArguments($className);
        $this->context = $arguments['context'];
        $arguments['filter'] = $this->filterBuilder =
            $this->objectManagerHelper->getObject(\Magento\Framework\Api\FilterBuilder::class);
        $this->filterGroupBuilder = $arguments['filterGroup'] =
        $this->filterBuilder = $this->objectManagerHelper
            ->getObject(\Magento\Framework\Api\Search\FilterGroupBuilder::class);
        $this->searchCriteriaBuilder = $arguments['search'] =
            $this->objectManagerHelper->getObject(\Magento\Framework\Api\Search\SearchCriteriaBuilder::class);
        $this->customerRepo = $arguments['customerInterface'];
        $this->ssmCartRepo = $arguments['ssmInterface'];

        $this->getCartForUser = $arguments['getCartForUser'];
        
        $this->getMockBuilder(\Gigya\GigyaIM\Model\Config::class)
            ->disableOriginalConstructor()->setMethods(['isDebugModeEnabled'])->getMock();
        $this->createEmptyCartForGuest = $arguments['createEmptyCartForGuest'];
        $this->storeManager = $arguments['storeManager'];

        $ssm = $this->getMockBuilder(\Abbott\GigyaIM\Api\Data\SsmCartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getId', 'setId',
                'setEmail', 'getEmail',
                'setWebsiteId', 'getWebsiteId',
                'setMaskedCartId', 'getMaskedCartId',
                'getCreatedAt', 'getUpdatedAt',
                'getExtensionAttributes', 'setExtensionAttributes'
            ])->getMock();
        $this->ssmCartInterface = $arguments['ssmCartInterface'] = $ssm;
        
        $this->cartManagement = $arguments['cartManagement'];
        $this->maskedQuoteIdToQuoteId = $arguments['maskedQuoteIdToQuoteId'];
        $this->cartRepository = $arguments['cartRepository'];
        $this->sortOrderBuilder = $arguments['sortOrderBuilder'] =
            $this->objectManagerHelper->getObject(\Magento\Framework\Api\SortOrderBuilder::class);
        $this->filterBuilder = $arguments['filter'];
        $this->quote = $arguments['quote'];
        
        $this->helper = $objectManagerHelper->getObject($className, $arguments);

        $this->ssmInterface = $this->getMockBuilder(\Abbott\GigyaIM\Api\Data\SsmCartInterface::class);
    }

    /**
     * @param $websiteId
     * @dataProvider getSsmProvider
     */
    public function testdeleteSsmCart($email, $websiteId)
    {
        $ssmData = $this->objectManagerHelper->getObject(\Abbott\GigyaIM\Model\SsmCart::class);
        $result = $this->getMockBuilder(\Abbott\GigyaIM\Api\Data\SsmCartSearchResultsInterface::class)
            ->disableOriginalConstructor()->getMock();
        $result->expects($this->any())->method('getItems')
            ->willReturn([$ssmData]);
        
        $this->ssmCartRepo->expects($this->any())->method('getList')
            ->willReturn($result);
        $this->helper->deleteSsmCart($email, $websiteId);
    }

    public function getSsmProvider()
    {
        return [
            [self::EMAIL, 1]
        ];
    }

    /**
     * @dataProvider getcustProvider
     */
    public function testFindGigyaCustomer($uid, $email, $websiteId)
    {
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $val = $this->getMockBuilder(\Magento\Framework\Api\AttributeInterface::class)
            ->disableOriginalConstructor()->getMock();
        $val->expects($this->any())->method('getValue')
            ->willReturn($uid);
        $customer->expects($this->any())->method('getCustomAttribute')
            ->with('gigya_uid')
            ->willReturn($val);
        $this->customerRepo->expects($this->any())->method('get')
            ->with($email, $websiteId)
            ->willReturn($customer);
        $this->assertEquals($customer, $this->helper->findGigyaCustomer($uid, $email, $websiteId));
    }

    /**
     * @dataProvider getcustProvider
     */
    public function testFindGigyaCustomer2($uid, $email, $websiteId)
    {
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $val = $this->getMockBuilder(\Magento\Framework\Api\AttributeInterface::class)
            ->disableOriginalConstructor()->getMock();
        $val->expects($this->any())->method('getValue')
        ->willReturn($uid);
        $customer->expects($this->any())->method('getCustomAttribute')
        ->with('gigya_uid')
        ->willReturn($val);
        $this->customerRepo->expects($this->any())->method('get')
            ->with($email, $websiteId)
            ->will(static::throwException(new \Magento\Framework\Exception\NoSuchEntityException()));
        $this->assertFalse($this->helper->findGigyaCustomer($uid, $email, $websiteId));
    }

    /**
     * @dataProvider getcustProvider
     */
    public function testFindGigyaCustomer3($uid, $email, $websiteId)
    {
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $val = $this->getMockBuilder(\Magento\Framework\Api\AttributeInterface::class)
            ->disableOriginalConstructor()->getMock();
        $val->expects($this->any())->method('getValue')
        ->willReturn($uid);
        $customer->expects($this->any())->method('getCustomAttribute')
        ->with('gigya_uid')
        ->willReturn($val);
        $this->customerRepo->expects($this->any())->method('get')
        ->with($email, $websiteId)
            ->will(static::throwException(new \Magento\Framework\Exception\LocalizedException(__("EXP"))));
        $this->assertFalse($this->helper->findGigyaCustomer($uid, $email, $websiteId));
    }

    public function getcustProvider()
    {
        return [
            ['sdsdh232b', self::EMAIL, 1],
            ['sdsdh232b', 'xyz@ab.com', 5]
        ];
    }

    /**
     * @dataProvider getCartProvider
     */
    public function testSetCart($email, $websiteId, $guestCart)
    {
        $maskedQuoteId = "fafrt34545";

        $ssmData = $this->getMockBuilder(\Abbott\GigyaIM\Api\Data\SsmCartInterface::class)
            ->disableOriginalConstructor()->getMock();
        if (empty($guestCart)) {
            if ($email == self::TEST_EMAIL) {
                $maskedQuoteId = $maskedQuoteId . "sds";
                $ssmData->expects($this->any())->method('getMaskedCartId')->willReturn($maskedQuoteId);
            } else {
                $ssmData->expects($this->any())->method('getMaskedCartId')->willReturn($maskedQuoteId);
            }
        }
        $result = $this->getMockBuilder(\Abbott\GigyaIM\Api\Data\SsmCartSearchResultsInterface::class)
            ->disableOriginalConstructor()->getMock();
        
        $this->ssmCartInterface->expects($this->any())->method('setEmail')
        ->with($email)->willReturn($this->ssmCartInterface);
        $this->ssmCartInterface->expects($this->any())->method('setWebsiteId')
        ->with($websiteId)->willReturn($this->ssmCartInterface);
        $this->ssmCartInterface->expects($this->any())->method('setMaskedCartId')
        ->with($guestCart)->willReturn($this->ssmCartInterface);
            
        if ($guestCart && $email == self::TEST_EMAIL) {
            $result->expects($this->any())->method('getItems')
                ->willReturn([null]);
        } elseif ($guestCart && $email != "pqr@abc.com") {
            $maskedQuoteId = $guestCart;
            $result->expects($this->any())->method('getItems')
                ->willReturn([null]);
        } elseif ($email == "pqr@abc.com") {
            $maskedQuoteId = "";
            $result->expects($this->any())->method('getItems')
                ->willReturn([null]);
            $this->getCartForUser->expects($this->once())
                ->method('execute')
                ->with($maskedQuoteId, 0, 1)
                ->willReturn($this->returnValue($maskedQuoteId));
        } else {
            $result->expects($this->any())->method('getItems')
                ->willReturn([$ssmData]);
        }

        $this->ssmCartRepo->expects($this->any())->method('getList')
        ->willReturn($result);
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $this->assertEquals($maskedQuoteId, $this->helper->setCart($email, $websiteId, $guestCart));
    }

    public function getCartProvider()
    {
        return [
            [self::EMAIL, 1, null],
            [self::TEST_EMAIL, 1, null],
            ['xyz@ab.com', 5, 'sdsdh232b']
        ];
    }

    /**
     * @dataProvider getCartProvider2
     */
    public function testSetCart2($email, $websiteId, $guestCart)
    {
        $maskedQuoteId = "ponw93bq0";

        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $ssmData = $this->getMockBuilder(\Abbott\GigyaIM\Api\Data\SsmCartInterface::class)
            ->disableOriginalConstructor()->getMock();

        if (empty($guestCart)) {
            $ssmData->expects($this->any())->method('getMaskedCartId')->willReturn($maskedQuoteId);
        }
        $result = $this->getMockBuilder(\Abbott\GigyaIM\Api\Data\SsmCartSearchResultsInterface::class)
            ->disableOriginalConstructor()->getMock();
        $result->expects($this->any())->method('getItems')
            ->willReturn([null]);

        $this->ssmCartInterface->expects($this->any())->method('setEmail')
        ->with($email)->willReturn($this->ssmCartInterface);
        $this->ssmCartInterface->expects($this->any())->method('setWebsiteId')
        ->with($websiteId)->willReturn($this->ssmCartInterface);
        $this->ssmCartInterface->expects($this->any())->method('setMaskedCartId')
        ->with($guestCart)->willReturn($this->ssmCartInterface);

        $this->ssmCartRepo->expects($this->any())->method('getList')
            ->willReturn($result);

        $this->createEmptyCartForGuest->expects($this->once())
            ->method('execute')
            ->with(null)
            ->willReturn("nnnnn");
        
        $this->assertEquals("nnnnn", $this->helper->setCart($email, $websiteId, $guestCart));
    }

    public function getCartProvider2()
    {
        return [
            ['pqr@ab.com', 5, null]
        ];
    }
}
