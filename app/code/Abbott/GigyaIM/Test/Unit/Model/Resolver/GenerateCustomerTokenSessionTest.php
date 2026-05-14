<?php

namespace Abbott\GigyaIM\Test\Unit\Model\Resolver;

use Abbott\GigyaIM\Model\Resolver\GenerateCustomerTokenSession;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Exception\NoSuchEntityException;

class GenerateCustomerTokenSessionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    public $objectManagerHelper;
    /**
     * @var class-string<Abbott\GigyaIM\Model\Resolver\GenerateCustomerTokenSession>
     */
    public $className;
    /**
     * @var mixed[]
     */
    public $arguments;
    public $fieldMock;
    public $contextMock;
    public $resolveInfoMock;
    /**
     * @var (\Abbott\AwsLambda\Logger\Log & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $loggerMock;
    /**
     * @var (\Magento\Customer\Model\Session & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $customerSessionMock;
    public $registrationMock;
    public $tokenModelMock;
    public $tokenMock;
    public $cartManagementMock;
    /**
     * @var (\Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $quoteIdToMaskedQuoteIdMock;
    public $quoteIdMaskFactoryMock;
    /**
     * @var (\Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $quoteIdMaskResourceMock;
    /**
     * @var (\Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $createEmptyCartForCustomerMock;
    /**
     * @var (\Abbott\MyAccount\Model\MergeCart & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $mergeCartMock;
    /**
     * @var (\Abbott\MyAccount\Helper\Data & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $accountHelperMock;
    public $cookieMetadataFactoryMock;
    /**
     * @var (\Magento\Framework\Stdlib\CookieManagerInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $cookieManagerMock;
    /**
     * @var (\Magento\Checkout\Model\Session & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $checkoutSessionMock;
    public $gigyaMageHelperMock;
    public $customerHelperMock;
    public $orderCollectionMock;
    public $orderColl;
    public $profileCollectionMock;
    public $profileColl;
    public $rmaCollectionMock;
    public $customerRepoMock;
    public $customerMock;
    public $quoteIdMaskMock;
    public $publicMetaMock;
    public $tokenSession;
    public $storeManager;
    public $store;
    public $extensionAttrMock;
    /**
     * @var string
     */
    public $validToken;
    /**
     * @var string
     */
    public $inValidToken;
    public $block;
    public function setup()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->className = \Abbott\GigyaIM\Model\Resolver\GenerateCustomerTokenSession::class;
        $this->arguments = $this->objectManagerHelper->getConstructArguments($this->className);
        $this->init();
    }

    public function init()
    {
        $this->fieldMock = $this->getMockBuilder(\Magento\Framework\GraphQl\Config\Element\Field::class)
            ->disableOriginalConstructor()->getMock();
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\GraphQl\Query\ContextInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->resolveInfoMock = $this->getMockBuilder(\Magento\Framework\GraphQl\Schema\Type\ResolveInfo::class)
            ->disableOriginalConstructor()->getMock();
        $this->loggerMock = $this->getMockBuilder(\Abbott\AwsLambda\Logger\Log::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()->getMock();
        $this->registrationMock = $this->getMockBuilder(\Magento\Customer\Model\Registration::class)
            ->disableOriginalConstructor()->getMock();
        $this->tokenModelMock = $this->getMockBuilder(\Magento\Integration\Model\Oauth\TokenFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->tokenMock = $this->getMockBuilder(\Magento\Integration\Model\Oauth\Token::class)
        ->disableOriginalConstructor()->getMock();
        $this->cartManagementMock = $this->createMock(\Magento\Quote\Api\CartManagementInterface::class);
        $this->quoteIdToMaskedQuoteIdMock = $this->getMockBuilder(
            \Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface::class
        )->disableOriginalConstructor()->setMethods(['execute'])->getMock();
        $this->quoteIdMaskFactoryMock = $this->getMockBuilder(\Magento\Quote\Model\QuoteIdMaskFactory::class)
            ->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $this->quoteIdMaskResourceMock = $this->getMockBuilder(
            \Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask::class
        )->disableOriginalConstructor()->setMethods(['save'])->getMock();
        $this->createEmptyCartForCustomerMock = $this->getMockBuilder(
            \Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer::class
        )->disableOriginalConstructor()->setMethods(['execute'])->getMock();
        $this->mergeCartMock = $this->getMockBuilder(\Abbott\MyAccount\Model\MergeCart::class)
            ->disableOriginalConstructor()->setMethods(['mergeCarts'])->getMock();
        $this->accountHelperMock = $this->getMockBuilder(\Abbott\MyAccount\Helper\Data::class)
        ->disableOriginalConstructor()->setMethods(['getCookieRedirect'])->getMock();
        $this->cookieMetadataFactoryMock = $this->getMockBuilder(
            \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
        )->disableOriginalConstructor()->setMethods(
            ['createCookieMetadata', 'createPublicCookieMetadata']
        )->getMock();
        $this->cookieManagerMock = $this->createMock(\Magento\Framework\Stdlib\CookieManagerInterface::class);
        $this->checkoutSessionMock = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
        ->disableOriginalConstructor()->getMock();
        $this->gigyaMageHelperMock = $this->getMockBuilder(\Abbott\GigyaIM\Helper\Data::class)
        ->disableOriginalConstructor()->setMethods(['isGigyaEnabledForWebsite'])->getMock();
        $this->customerHelperMock = $this->getMockBuilder(\Abbott\GigyaIM\Helper\CustomerHelper::class)
        ->disableOriginalConstructor()->setMethods(['setCart'])->getMock();
        $this->customerHelperMock = $this->createMock(\Abbott\GigyaIM\Helper\CustomerHelper::class);
        $this->orderCollectionMock = $this->getMockBuilder(
            \Magento\Sales\Model\ResourceModel\Order\CollectionFactory::class
        )->disableOriginalConstructor()->getMock();
        $this->orderColl = $this->createMock(
            \Magento\Sales\Model\ResourceModel\Order\Collection::class
        );
        $this->profileCollectionMock = $this->getMockBuilder(
            \Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory::class
        )->disableOriginalConstructor()->getMock();
        $this->profileColl = $this->createMock(
            \Aheadworks\Sarp2\Model\ResourceModel\Profile\Collection::class
        );
        $this->rmaCollectionMock = $this->getMockBuilder(
            \Magento\Rma\Model\ResourceModel\Rma\Collection::class
        )->disableOriginalConstructor()->getMock();
        $this->customerRepoMock = $this->getMockBuilder(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->customerRepoMock = $this->createMock(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );

        $this->customerMock = $this->createMock(
            \Magento\Customer\Api\Data\CustomerInterface::class
        );

        $this->quoteIdMaskMock = $this->createMock(\Magento\Quote\Model\QuoteIdMask::class);
        $this->quoteIdMaskFactoryMock->method('create')->willReturn($this->quoteIdMaskMock);

        $this->publicMetaMock = $this->createMock(\Magento\Framework\Stdlib\Cookie\PublicCookieMetadata::class);

        $this->cookieMetadataFactoryMock->method('createPublicCookieMetadata')
        ->willReturn($this->publicMetaMock);

        $this->customerHelperMock->method('setCart')
            ->will($this->returnValue("ncmvbcmbv"));

        $this->arguments = [
            'customerSession' => $this->customerSessionMock,
            'registration' => $this->registrationMock,
            'gigyaHelper' => $this->gigyaMageHelperMock,
            'tokenModelFactory' => $this->tokenModelMock,
            'cartManagement' => $this->cartManagementMock,
            'quoteIdToMaskedQuoteId' => $this->quoteIdToMaskedQuoteIdMock,
            'createEmptyCartForCustomer' => $this->createEmptyCartForCustomerMock,
            'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock,
            'quoteIdMaskResourceModel' => $this->quoteIdMaskResourceMock,
            'mergeCartModel' => $this->mergeCartMock,
            'logger' => $this->loggerMock,
            'accountHelper' => $this->accountHelperMock,
            'cookieMetadataFactory' => $this->cookieMetadataFactoryMock,
            'cookieManagerInterface' => $this->cookieManagerMock,
            'orderCollectionFactory' => $this->orderCollectionMock,
            'profileCollectionFactory' => $this->profileCollectionMock,
            'rmaCollection' => $this->rmaCollectionMock,
            'customerHelper' => $this->customerHelperMock,
            'customerRepository' => $this->customerRepoMock,
            'checkoutSession' => $this->checkoutSessionMock
        ];

        $this->tokenSession = $this->objectManagerHelper->getObject($this->className, $this->arguments);

        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $this->store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->store->expects($this->any())->method('getId')->will($this->returnValue(1));

        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($this->store));

        $this->gigyaMageHelperMock->method('isGigyaEnabledForWebsite')->will($this->returnValue(1));

        $this->extensionAttrMock = $this->createMock(\Magento\GraphQl\Model\Query\ContextExtensionInterface::class);

        $this->extensionAttrMock->method('getStore')->willReturn($this->store);

        $this->contextMock = $this->getMockBuilder(\Magento\GraphQl\Query\ContextInterface::class)
            ->setMethods(['getUserId', 'getExtensionAttributes'])
            ->disableOriginalConstructor()->getMock();

        $this->contextMock->method('getExtensionAttributes')
            ->willReturn($this->extensionAttrMock);

        $this->validToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6IlJFUTBNVVE1TjBOQ1JUSkVNemszTTBVMVJrTkRRMFUwUTBNMVJFRkJSamhETWpkRU5VRkJRZyIsImtleWlkIjoiUkVRME1VUTVOME5DUlRKRU16azNNMFUxUmtORFEwVTBRME0xUkVGQlJqaERNamRFTlVGQlFnIn0.eyJpc3MiOiJodHRwczovL2ZpZG0uZ2lneWEuY29tL2p3dC8zX0ROdmtuckQ0RkNmQ0Q1WDNHaXRZUWJEcHA3QXpHRV9MZkFjaEw5d1ZUT185Uk81ZmpLYkkyWUhvREkxSTE1X28vIiwiYXBpS2V5IjoiM19ETnZrbnJENEZDZkNENVgzR2l0WVFiRHBwN0F6R0VfTGZBY2hMOXdWVE9fOVJPNWZqS2JJMllIb0RJMUkxNV9vIiwiaWF0IjoxNTg0NjI1NDQ2LCJleHAiOjE1ODQ2MjU3NDYsInN1YiI6ImUyNDMwYThhNjcwZTRiZTE4MzAzOGQ4ZTRkMGRhZDNiIn0.osNr14Oqixq_J3WudNXATxx0uJF0Wsvn8ZdmgDh5U1m69UU2vOebTluQ1ldsCIwcFivJCnea6syU_GE1VeaooDHkgTFOOoovJqJ5RE33axI7g2Gu_H2TV2IS7YAXFYEh7fNv7A5adJpl5O_ND0PSwg1c_KlgvbH92RjPiOpvEZxspBIleQJquM4ThdtQ_mNtKSubdLY2PMA31Q7lAVKxQzHfTkuQFOZj72RjUlpYt_JEqJNzoabq625j1GXqQTVYWfi-j7Zm6NLXujXwHKkeKA40jZzdml4IuFhrP1Hdd7RvBS5j4ndiz5oetJFS8f8LuhyJsRHPlBiLUc6CONKv-A";

        $this->inValidToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6IlJFUTBNVVE1TjBOQ1JUSkVNemszTTBVMVJrTkRRMFUwUTBNMVJFRkJSamhETWpkRU5VRkJRZyIsImtleWlkIjoiUkVRME1VUTVOME5DUlRKRU16azNNMFUxUmtORFEwVTBRME0xUkVGQlJqaERNamRFTlVGQlFnIn0eyJpc3MiOiJodHRwczovL2ZpZG0uZ2lneWEuY29tL2p3dC8zX0ROdmtuckQ0RkNmQ0Q1WDNHaXRZUWJEcHA3QXpHRV9MZkFjaEw5d1ZUT185Uk81ZmpLYkkyWUhvREkxSTE1X28vIiwiYXBpS2V5IjoiM19ETnZrbnJENEZDZkNENVgzR2l0WVFiRHBwN0F6R0VfTGZBY2hMOXdWVE9fOVJPNWZqS2JJMllIb0RJMUkxNV9vIiwiaWF0IjoxNTg0NjI1NDQ2LCJleHAiOjE1ODQ2MjU3NDYsInN1YiI6ImUyNDMwYThhNjcwZTRiZTE4MzAzOGQ4ZTRkMGRhZDNiIn0.osNr14Oqixq_J3WudNXATxx0uJF0Wsvn8ZdmgDh5U1m69UU2vOebTluQ1ldsCIwcFivJCnea6syU_GE1VeaooDHkgTFOOoovJqJ5RE33axI7g2Gu_H2TV2IS7YAXFYEh7fNv7A5adJpl5O_ND0PSwg1c_KlgvbH92RjPiOpvEZxspBIleQJquM4ThdtQ_mNtKSubdLY2PMA31Q7lAVKxQzHfTkuQFOZj72RjUlpYt_JEqJNzoabq625j1GXqQTVYWfi-j7Zm6NLXujXwHKkeKA40jZzdml4IuFhrP1Hdd7RvBS5j4ndiz5oetJFS8f8LuhyJsRHPlBiLUc6CONKv-A";
    }

    public function testResolveExp1()
    {
        $this->contextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn("232ftyt");
        $exp = $this->expectException(GraphQlInputException::class);
        $this->assertEquals($exp, $this->tokenSession->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            [],
            []
        ));
    }

    public function testResolveExp2()
    {
        $this->contextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn("");
        $exp = $this->expectException(GraphQlInputException::class);
        $this->assertEquals($exp, $this->tokenSession->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            [],
            []
        ));
    }

    public function testResolveExp3()
    {
        $this->contextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn("");
        $exp = $this->expectException(GraphQlInputException::class);
        $return = $this->tokenSession->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            [],
            []
        );
        $this->assertEquals($exp, $return);
    }

    public function testResolveExp4()
    {
        $this->contextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn("");
        $args['input']['gigya_id_token'] = "abc";
        $exp = $this->expectException(GraphQlInputException::class);
        $return = $this->tokenSession->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            [],
            $args
        );
        $this->assertEquals($exp, $return);
    }

    public function testResolveExp5()
    {
        $this->contextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn("");
        $args['input']['gigya_id_token'] = $this->inValidToken;
        $args['input']['gigya_user']['email'] = "abc@abc.com";
        $exp = $this->expectException(GraphQlInputException::class);
        $return = $this->tokenSession->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            [],
            $args
        );
        $this->assertEquals($exp, $return);
    }

    public function testResolveExp6()
    {
        $this->contextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn("");
        $args['input']['gigya_id_token'] = "kfkdsfkjdsgfjkg";
        $args['input']['gigya_user']['email'] = "abc@abc.com";
        $exp = $this->expectException(GraphQlInputException::class);
        $return = $this->tokenSession->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            [],
            $args
        );
        $this->assertEquals($exp, $return);
    }

    public function testResolve1()
    {
        $this->contextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn("");
        $args['input']['gigya_id_token'] = $this->validToken;
        $args['input']['gigya_user']['email'] = "abc@abc.com";

        $return = $this->tokenSession->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            [],
            $args
        );
    }

    public function testResolve2()
    {
        $email = "abc@abc.com";
        $this->contextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn("");
        $args['input']['gigya_id_token'] = $this->validToken;
        $args['input']['gigya_user']['email'] = "abc@abc.com";

        $this->registrationMock->method('isAllowed')->will($this->returnValue(1));

        $this->customerRepoMock->method('get')
            ->with(
                $email,
                1
            )
            ->willReturn(false);

        $return = $this->tokenSession->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            [],
            $args
        );
        $this->assertEquals(false, $return);
    }

    public function testResolve3()
    {
        $email = "abc@abc.com";
        $this->contextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn("");
        $args['input']['gigya_id_token'] = $this->validToken;
        $args['input']['gigya_user']['email'] = $email;

        $this->registrationMock->method('isAllowed')->will($this->returnValue(1));

        $this->customerRepoMock->method('get')
            ->with(
                $email,
                1
            )
            ->willReturn(false);

        $return = $this->tokenSession->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            [],
            $args
        );
        $this->assertEquals(false, $return);
    }

    public function testResolve4()
    {
        $email = "abc@abc.com";
        $this->contextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn("");
        $args['input']['gigya_id_token'] = $this->validToken;
        $args['input']['gigya_user']['email'] = $email;

        $this->registrationMock->method('isAllowed')->will($this->returnValue(1));

        $this->customerHelperMock->method('findGigyaCustomer')
        ->willReturn($this->customerMock);

        $this->tokenModelMock->method('create')->willReturn($this->tokenMock);

        $this->tokenMock->method('createCustomerToken')->willReturnSelf();

        $this->customerRepoMock->method('get')
            ->with(
                $email,
                1
            )
            ->willReturn(false);

        $this->orderCollectionMock->method('create')->willReturn($this->orderColl);
        $this->orderColl->method('addFieldToFilter')->willReturnSelf();
        $this->orderColl->method('getSize')->will($this->returnValue(1));

        $this->profileCollectionMock->method('create')->willReturn($this->profileColl);
        $this->profileColl->method('addFieldToFilter')->willReturnSelf();
        $this->profileColl->method('getSize')->will($this->returnValue(1));

        $this->rmaCollectionMock->method('addFieldToFilter')->willReturnSelf();
        $this->rmaCollectionMock->method('load')->willReturnSelf();

        $cart = $this->createMock(\Magento\Quote\Api\Data\CartInterface::class);
        $this->cartManagementMock->method('getCartForCustomer')->willReturn($cart);

        $return = $this->tokenSession->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            [],
            $args
        );
        $this->assertEquals(true, $return);
    }

    public function testResolveExp7()
    {
        $email = "abc@abc.com";
        $this->contextMock->expects($this->once())
            ->method('getUserId')
            ->willReturn("");
        $args['input']['gigya_id_token'] = $this->validToken;
        $args['input']['gigya_user']['email'] = $email;

        $this->registrationMock->method('isAllowed')->will($this->returnValue(1));

        $this->customerHelperMock->method('findGigyaCustomer')
        ->willReturn($this->customerMock);

        $this->tokenModelMock->method('create')->willReturn($this->tokenMock);

        $this->tokenMock->method('createCustomerToken')->willReturnSelf();

        $this->customerRepoMock->method('get')
            ->with(
                $email,
                1
            )
            ->willReturn(false);

        $this->orderCollectionMock->method('create')->willReturn($this->orderColl);
        $this->orderColl->method('addFieldToFilter')->willReturnSelf();
        $this->orderColl->method('getSize')->will($this->returnValue(1));

        $this->profileCollectionMock->method('create')->willReturn($this->profileColl);
        $this->profileColl->method('addFieldToFilter')->willReturnSelf();
        $this->profileColl->method('getSize')->will($this->returnValue(1));

        $this->rmaCollectionMock->method('addFieldToFilter')->willReturnSelf();
        $this->rmaCollectionMock->method('load')->willReturnSelf();

        $cart = $this->createMock(\Magento\Quote\Api\Data\CartInterface::class);
        $this->cartManagementMock->method('getCartForCustomer')
        ->with("1")
        ->willThrowException(new NoSuchEntityException(
            new \Magento\Framework\Phrase('No cart found.')
        ));

        $return = $this->tokenSession->resolve(
            $this->fieldMock,
            $this->contextMock,
            $this->resolveInfoMock,
            [],
            $args
        );
        $this->assertEquals(false, $return);
    }

    public function ttestValidateGigyaIdToken(): void
    {
        $testMethod = new \ReflectionMethod(\Abbott\GigyaIM\Model\Resolver\GenerateCustomerTokenSession::class, 'validateGigyaIdToken');
        $testMethod->setAccessible(true);
        $this->assertEquals(true, $testMethod->invokeArgs($this->block, ["eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6IlJFUTBNVVE1TjBOQ1JUSkVNemszTTBVMVJrTkRRMFUwUTBNMVJFRkJSamhETWpkRU5VRkJRZyIsImtleWlkIjoiUkVRME1VUTVOME5DUlRKRU16azNNMFUxUmtORFEwVTBRME0xUkVGQlJqaERNamRFTlVGQlFnIn0.eyJpc3MiOiJodHRwczovL2ZpZG0uZ2lneWEuY29tL2p3dC8zX0ROdmtuckQ0RkNmQ0Q1WDNHaXRZUWJEcHA3QXpHRV9MZkFjaEw5d1ZUT185Uk81ZmpLYkkyWUhvREkxSTE1X28vIiwiYXBpS2V5IjoiM19ETnZrbnJENEZDZkNENVgzR2l0WVFiRHBwN0F6R0VfTGZBY2hMOXdWVE9fOVJPNWZqS2JJMllIb0RJMUkxNV9vIiwiaWF0IjoxNTg0NjI1NDQ2LCJleHAiOjE1ODQ2MjU3NDYsInN1YiI6ImUyNDMwYThhNjcwZTRiZTE4MzAzOGQ4ZTRkMGRhZDNiIn0.osNr14Oqixq_J3WudNXATxx0uJF0Wsvn8ZdmgDh5U1m69UU2vOebTluQ1ldsCIwcFivJCnea6syU_GE1VeaooDHkgTFOOoovJqJ5RE33axI7g2Gu_H2TV2IS7YAXFYEh7fNv7A5adJpl5O_ND0PSwg1c_KlgvbH92RjPiOpvEZxspBIleQJquM4ThdtQ_mNtKSubdLY2PMA31Q7lAVKxQzHfTkuQFOZj72RjUlpYt_JEqJNzoabq625j1GXqQTVYWfi-j7Zm6NLXujXwHKkeKA40jZzdml4IuFhrP1Hdd7RvBS5j4ndiz5oetJFS8f8LuhyJsRHPlBiLUc6CONKv-A"]), "Success - Validate Gigya ID token against public token");
        $this->assertEquals(false, $testMethod->invokeArgs($this->block, ["rtJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6IlJFUTBNVVE1TjBOQ1JUSkVNemszTTBVMVJrTkRRMFUwUTBNMVJFRkJSamhETWpkRU5VRkJRZyIsImtleWlkIjoiUkVRME1VUTVOME5DUlRKRU16azNNMFUxUmtORFEwVTBRME0xUkVGQlJqaERNamRFTlVGQlFnIn0.eyJpc3MiOiJodHRwczovL2ZpZG0uZ2lneWEuY29tL2p3dC8zX0ROdmtuckQ0RkNmQ0Q1WDNHaXRZUWJEcHA3QXpHRV9MZkFjaEw5d1ZUT185Uk81ZmpLYkkyWUhvREkxSTE1X28vIiwiYXBpS2V5IjoiM19ETnZrbnJENEZDZkNENVgzR2l0WVFiRHBwN0F6R0VfTGZBY2hMOXdWVE9fOVJPNWZqS2JJMllIb0RJMUkxNV9vIiwiaWF0IjoxNTg0NjI1NDQ2LCJleHAiOjE1ODQ2MjU3NDYsInN1YiI6ImUyNDMwYThhNjcwZTRiZTE4MzAzOGQ4ZTRkMGRhZDNiIn0.osNr14Oqixq_J3WudNXATxx0uJF0Wsvn8ZdmgDh5U1m69UU2vOebTluQ1ldsCIwcFivJCnea6syU_GE1VeaooDHkgTFOOoovJqJ5RE33axI7g2Gu_H2TV2IS7YAXFYEh7fNv7A5adJpl5O_ND0PSwg1c_KlgvbH92RjPiOpvEZxspBIleQJquM4ThdtQ_mNtKSubdLY2PMA31Q7lAVKxQzHfTkuQFOZj72RjUlpYt_JEqJNzoabq625j1GXqQTVYWfi-j7Zm6NLXujXwHKkeKA40jZzdml4IuFhrP1Hdd7RvBS5j4ndiz5oetJFS8f8LuhyJsRHPlBiLUc6CONKv-A"]), "Failure - Validate Gigya ID token against public token");
    }

    public function ttestDecodeGigyaIdToken(): void
    {
        $testMethod = new \ReflectionMethod(\Abbott\GigyaIM\Model\Resolver\GenerateCustomerTokenSession::class, 'decodeGigyaIdToken');
        $testMethod->setAccessible(true);
        $gigya_obj = $testMethod->invokeArgs($this->block, ["eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6IlJFUTBNVVE1TjBOQ1JUSkVNemszTTBVMVJrTkRRMFUwUTBNMVJFRkJSamhETWpkRU5VRkJRZyIsImtleWlkIjoiUkVRME1VUTVOME5DUlRKRU16azNNMFUxUmtORFEwVTBRME0xUkVGQlJqaERNamRFTlVGQlFnIn0.eyJpc3MiOiJodHRwczovL2ZpZG0uZ2lneWEuY29tL2p3dC8zX0ROdmtuckQ0RkNmQ0Q1WDNHaXRZUWJEcHA3QXpHRV9MZkFjaEw5d1ZUT185Uk81ZmpLYkkyWUhvREkxSTE1X28vIiwiYXBpS2V5IjoiM19ETnZrbnJENEZDZkNENVgzR2l0WVFiRHBwN0F6R0VfTGZBY2hMOXdWVE9fOVJPNWZqS2JJMllIb0RJMUkxNV9vIiwiaWF0IjoxNTg0NjI1NDQ2LCJleHAiOjE1ODQ2MjU3NDYsInN1YiI6ImUyNDMwYThhNjcwZTRiZTE4MzAzOGQ4ZTRkMGRhZDNiIn0.osNr14Oqixq_J3WudNXATxx0uJF0Wsvn8ZdmgDh5U1m69UU2vOebTluQ1ldsCIwcFivJCnea6syU_GE1VeaooDHkgTFOOoovJqJ5RE33axI7g2Gu_H2TV2IS7YAXFYEh7fNv7A5adJpl5O_ND0PSwg1c_KlgvbH92RjPiOpvEZxspBIleQJquM4ThdtQ_mNtKSubdLY2PMA31Q7lAVKxQzHfTkuQFOZj72RjUlpYt_JEqJNzoabq625j1GXqQTVYWfi-j7Zm6NLXujXwHKkeKA40jZzdml4IuFhrP1Hdd7RvBS5j4ndiz5oetJFS8f8LuhyJsRHPlBiLUc6CONKv-A"]);
        $this->assertEquals("e2430a8a670e4be183038d8e4d0dad3b", $gigya_obj->sub, "Success - Fetch Gigya UID by decoding Gigya ID token");
        $gigya_obj = $testMethod->invokeArgs($this->block, ["abcde.fghij.klmno"]);
        $this->assertTrue(!isset($gigya_obj->sub), "Failure - Fetch Gigya UID by decoding Gigya ID token");
    }
}
