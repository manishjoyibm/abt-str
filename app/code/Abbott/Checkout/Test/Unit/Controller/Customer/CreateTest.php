<?php
namespace Abbott\Checkout\Test\Unit\Controller\Customer;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\InputException;

class CreateTest extends \PHPUnit\Framework\TestCase
{
    public $redirectMock;
    public $responseMock;
    public $requestMock;
    public $messageManagerMock;
    public $resultRedirectFactoryMock;
    public $resultRedirectMock;
    public $customerSession;
    public $registrationMock;
    public $response;
    public $request;
    public $redirectResultMock;
    public $redirectFactoryMock;
    public $resultPageMock;
    public $pageFactoryMock;
    public $storeManagerMock;
    public $storeMock;
    public $awsHelperMock;
    public $cookieManagerMock;
    public $regionFacMock;
    public $regionMock;
    public $formFactoryMock;
    public $formMock;
    public $regionFactoryMock;
    public $regionDataMock;
    public $addressFactoryMock;
    public $addressDataMock;
    public $customerExtractorMock;
    /**
     * @var (\Magento\Customer\Model\Url & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $customerUrl;
    /**
     * @var (\Magento\Customer\Model\Registration & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $registration;
    /**
     * @var (\Magento\Framework\Api\DataObjectHelper & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $dataObjectHelperMock;
    public $customerMock;
    public $accountManagement;
    public $customerRepository;
    public $object;
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $className = \Abbott\Checkout\Controller\Customer\Create::class;
        $arguments = $objectManager->getConstructArguments($className);

        $this->redirectMock = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);
        $this->responseMock = $this->createMock(\Magento\Framework\Webapi\Response::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);

        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\Manager::class);
        $eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);

        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            \Magento\Framework\Controller\Result\RedirectFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->createMock(
            \Magento\Framework\Controller\Result\Redirect::class
        );
        $this->resultRedirectMock->method('setPath')->willReturnSelf();
        $this->resultRedirectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $contextMock = $this->createMock(\Magento\Framework\App\Action\Context::class);
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $contextMock->expects($this->any())
            ->method('getRedirect')
            ->willReturn($this->redirectMock);
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $contextMock->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManagerMock);
        $contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);
        $arguments['context'] = $contextMock;

        $this->customerSession = $this->createMock(\Magento\Customer\Model\Session::class);
        $arguments['customerSession'] = $this->customerSession;

        $this->registrationMock = $this->createMock(\Magento\Customer\Model\Registration::class);
        $this->redirectMock = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);
        $this->response = $this->createMock(\Magento\Framework\App\ResponseInterface::class);
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->redirectResultMock = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);

        $this->redirectFactoryMock = $this->createPartialMock(
            \Magento\Framework\Controller\Result\RedirectFactory::class,
            ['create']
        );
        $arguments['resultRedirectFactory'] = $this->resultRedirectFactoryMock;

        $this->resultPageMock = $this->createMock(\Magento\Framework\View\Result\Page::class);
        $this->pageFactoryMock = $this->createMock(\Magento\Framework\View\Result\PageFactory::class);

        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeManagerMock->method('getStore')->willReturn($this->storeMock);
        $arguments['storeManager'] = $this->storeManagerMock;

        $this->awsHelperMock = $this->createMock(\Abbott\AwsLambda\Helper\Data::class);
        $this->awsHelperMock->method('isCreateCustomerEnabled')->willReturn(true);
        $arguments['helper'] = $this->awsHelperMock;

        $this->cookieManagerMock = $this->createMock(\Magento\Framework\Stdlib\CookieManagerInterface::class);
        $arguments['cookieManagerInterface'] = $this->cookieManagerMock;

        $this->regionFacMock = $this->createMock(\Magento\Directory\Model\RegionFactory::class);
        $this->regionMock = $this->createMock(\Magento\Directory\Model\Region::class);
        $this->regionMock->method('loadByCode')->willReturnSelf();
        $this->regionFacMock->method('create')->willReturn($this->regionMock);
        $arguments['regionFactory'] = $this->regionFacMock;

        $this->formFactoryMock = $this->createMock(\Magento\Customer\Model\Metadata\FormFactory::class);
        $this->formMock = $this->createMock(\Magento\Customer\Model\Metadata\Form::class);
        $this->formFactoryMock->method('create')->willReturn($this->formMock);
        $arguments['formFactory'] = $this->formFactoryMock;

        $this->regionFactoryMock = $this->createMock(\Magento\Customer\Api\Data\RegionInterfaceFactory::class);
        $this->regionDataMock = $this->createMock(\Magento\Customer\Api\Data\RegionInterface::class);
        $this->regionFactoryMock->method('create')->willReturn($this->regionDataMock);
        $arguments['regionDataFactory'] = $this->regionFactoryMock;

        $this->addressFactoryMock = $this->createMock(\Magento\Customer\Api\Data\AddressInterfaceFactory::class);
        $this->addressDataMock = $this->createMock(\Magento\Customer\Api\Data\AddressInterface::class);
        $this->addressFactoryMock->method('create')->willReturn($this->addressDataMock);
        $this->addressDataMock->method('setIsDefaultBilling')->willReturnSelf();
        $arguments['addressDataFactory'] = $this->addressFactoryMock;

        $this->customerExtractorMock = $this->createMock(\Magento\Customer\Model\CustomerExtractor::class);
        $arguments['customerExtractor'] = $this->customerExtractorMock;

        $this->customerUrl = $this->createMock(\Magento\Customer\Model\Url::class);
        $this->registration = $this->createMock(\Magento\Customer\Model\Registration::class);
        $this->dataObjectHelperMock = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);

        $this->customerMock = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);

        $this->accountManagement = $this->createMock(\Magento\Customer\Api\AccountManagementInterface::class);
        $arguments['accountManagement'] = $this->accountManagement;

        $this->customerRepository = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $arguments['customerInterface'] = $this->customerRepository;

        $this->object = $objectManager->getObject(
            $className,
            $arguments
        );

        [
            'request' => $this->request,
            'response' => $this->response,
            'customerSession' => $this->customerSession,
            'registration' => $this->registrationMock,
            'redirect' => $this->redirectMock,
            'resultRedirectFactory' => $this->redirectFactoryMock,
            'resultPageFactory' => $this->pageFactoryMock
        ];
    }

    /**
     * @return void
     */
    public function testIsLoggedIn1()
    {
        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));
        $this->object->execute();

        $this->registrationMock->expects($this->any())
            ->method('isAllowed')
            ->will($this->returnValue(false));

        $this->redirectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->redirectResultMock);
    }

    /**
     * @return void
     */
    public function testIsLoggedIn2()
    {
        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->object->execute();
    }

    /**
     * @return void
     */
    public function testIsLoggedIn3()
    {
        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->cookieManagerMock->expects($this->at(0))->method('getCookie')
            ->with('x-id-token')
            ->will($this->returnValue("123"));

        $this->cookieManagerMock->expects($this->at(1))->method('getCookie')
        ->with('abt_cartKey')
        ->will($this->returnValue("432"));

        $this->object->execute();
    }

    /**
     * @return void
     */
    public function testIsLoggedIn4()
    {
        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->cookieManagerMock->expects($this->at(0))->method('getCookie')
        ->with('x-id-token')
        ->will($this->returnValue("123"));

        $this->cookieManagerMock->expects($this->at(1))->method('getCookie')
        ->with('abt_cartKey')
        ->will($this->returnValue("432"));

        $this->awsHelperMock->method('getProfile')
            ->will($this->returnValue('
            {
                "status": true,
                "response" : {
                    "userInfo": {
                        "userName": "abc@abc.com",
                        "firstName": "ABC",
                        "lastName": "ABC",
                        "userType": "1"
                    },
                    "addresses": [
                        {
                            "country": "1",
                            "state": "Texas",
                            "lineOne": "AbC path",
                            "city": "Tex",
                            "zipCode": "87345"
                        }
                    ]
                }
            }
            '));

        $attr = $this->createMock(\Magento\Customer\Api\Data\AttributeMetadataInterface::class);
        $attr->method('getAttributeCode')->will($this->returnValue('region'));
        $arr[] = $attr;
        $this->formMock->method('getAllowedAttributes')->willReturn($arr);

        $this->customerExtractorMock->expects($this->once())
            ->method('extract')
            ->willReturn($this->customerMock);

        $ext = $this->createMock(\Magento\Customer\Api\Data\CustomerExtensionInterface::class);
        $this->customerMock->method('getExtensionAttributes')->willReturn($ext);
        $this->customerMock->method('getId')->willReturn($ext);

        $this->accountManagement->method('createAccount')->willReturn($this->customerMock);

        $this->object->execute();
    }

    /**
     * @return void
     */
    public function testIsLoggedIn5()
    {
        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->cookieManagerMock->expects($this->at(0))->method('getCookie')
        ->with('x-id-token')
        ->will($this->returnValue("123"));

        $this->cookieManagerMock->expects($this->at(1))->method('getCookie')
        ->with('abt_cartKey')
        ->will($this->returnValue("432"));

        $this->awsHelperMock->method('getProfile')
        ->will($this->returnValue('
            {
                "status": true,
                "response" : {
                    "userInfo": {
                        "userName": "abc@abc.com",
                        "firstName": "ABC",
                        "lastName": "ABC",
                        "userType": "1"
                    },
                    "addresses": [
                        {
                            "country": "1",
                            "state": "Texas",
                            "lineOne": "AbC path",
                            "city": "Tex",
                            "zipCode": "87345"
                        }
                    ]
                }
            }
            '));

        $attr = $this->createMock(\Magento\Customer\Api\Data\AttributeMetadataInterface::class);
        $attr->method('getAttributeCode')->will($this->returnValue('region'));
        $arr[] = $attr;
        $this->formMock->method('getAllowedAttributes')->willReturn($arr);

        $this->customerExtractorMock->expects($this->once())
            ->method('extract')
            ->willReturn($this->customerMock);

        $ext = $this->createMock(\Magento\Customer\Api\Data\CustomerExtensionInterface::class);
        $this->customerMock->method('getExtensionAttributes')->willReturn($ext);
        $this->customerMock->method('getId')->willReturn($ext);

        $this->accountManagement->method('createAccount')->willReturn($this->customerMock);

        $this->customerRepository->method('get')->willThrowException(new NoSuchEntityException(
            new \Magento\Framework\Phrase('No customer found.')
        ));

        $this->object->execute();
    }

    /**
     * @return void
     */
    public function testIsLoggedIn6()
    {
        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->cookieManagerMock->expects($this->at(0))->method('getCookie')
        ->with('x-id-token')
        ->will($this->returnValue("123"));

        $this->cookieManagerMock->expects($this->at(1))->method('getCookie')
        ->with('abt_cartKey')
        ->will($this->returnValue("432"));

        $this->awsHelperMock->method('getProfile')
        ->will($this->returnValue('
            {
                "status": true,
                "response" : {
                    "userInfo": {
                        "userName": "abc@abc.com",
                        "firstName": "ABC",
                        "lastName": "ABC",
                        "userType": "1"
                    },
                    "addresses": [
                        {
                            "country": "1",
                            "state": "Texas",
                            "lineOne": "AbC path",
                            "city": "Tex",
                            "zipCode": "87345"
                        }
                    ]
                }
            }
            '));

        $attr = $this->createMock(\Magento\Customer\Api\Data\AttributeMetadataInterface::class);
        $attr->method('getAttributeCode')->will($this->returnValue('region'));
        $arr[] = $attr;
        $this->formMock->method('getAllowedAttributes')->willReturn($arr);

        $this->customerExtractorMock->expects($this->once())
            ->method('extract')
            ->willReturn($this->customerMock);

        $ext = $this->createMock(\Magento\Customer\Api\Data\CustomerExtensionInterface::class);
        $this->customerMock->method('getExtensionAttributes')->willReturn($ext);
        $this->customerMock->method('getId')->willReturn($ext);

        $this->accountManagement->method('createAccount')->willReturn($this->customerMock);

        $this->customerRepository->method('get')->willThrowException(new LocalizedException(
            new \Magento\Framework\Phrase('No customer found.')
        ));

        $this->object->execute();
    }

    /**
     * @return void
     */
    public function testIsLoggedIn7()
    {
        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->cookieManagerMock->expects($this->at(0))->method('getCookie')
        ->with('x-id-token')
        ->will($this->returnValue("123"));

        $this->cookieManagerMock->expects($this->at(1))->method('getCookie')
        ->with('abt_cartKey')
        ->will($this->returnValue("432"));

        $this->awsHelperMock->method('getProfile')
        ->will($this->returnValue('
            {
                "status": true,
                "response" : {
                    "userInfo": {
                        "userName": "abc@abc.com",
                        "firstName": "ABC",
                        "lastName": "ABC",
                        "userType": "1"
                    },
                    "addresses": [
                        {
                            "country": "1",
                            "state": "Texas",
                            "lineOne": "AbC path",
                            "city": "Tex",
                            "zipCode": "87345"
                        }
                    ]
                }
            }
            '));

        $attr = $this->createMock(\Magento\Customer\Api\Data\AttributeMetadataInterface::class);
        $attr->method('getAttributeCode')->will($this->returnValue('region'));
        $arr[] = $attr;
        $this->formMock->method('getAllowedAttributes')->willReturn($arr);

        $this->customerExtractorMock->expects($this->once())
            ->method('extract')
            ->willReturn($this->customerMock);

        $ext = $this->createMock(\Magento\Customer\Api\Data\CustomerExtensionInterface::class);
        $this->customerMock->method('getExtensionAttributes')->willReturn($ext);
        $this->customerMock->method('getId')->willReturn($ext);

        $this->accountManagement->method('createAccount')->willThrowException(new StateException(
            new \Magento\Framework\Phrase('Customer found.')
        ));

        $this->object->execute();
    }

    /**
     * @return void
     */
    public function testIsLoggedIn8()
    {
        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->cookieManagerMock->expects($this->at(0))->method('getCookie')
        ->with('x-id-token')
        ->will($this->returnValue("123"));

        $this->cookieManagerMock->expects($this->at(1))->method('getCookie')
        ->with('abt_cartKey')
        ->will($this->returnValue("432"));

        $this->awsHelperMock->method('getProfile')
        ->will($this->returnValue('
            {
                "status": true,
                "response" : {
                    "userInfo": {
                        "userName": "abc@abc.com",
                        "firstName": "ABC",
                        "lastName": "ABC",
                        "userType": "1"
                    },
                    "addresses": [
                        {
                            "country": "1",
                            "state": "Texas",
                            "lineOne": "AbC path",
                            "city": "Tex",
                            "zipCode": "87345"
                        }
                    ]
                }
            }
            '));

        $attr = $this->createMock(\Magento\Customer\Api\Data\AttributeMetadataInterface::class);
        $attr->method('getAttributeCode')->will($this->returnValue('region'));
        $arr[] = $attr;
        $this->formMock->method('getAllowedAttributes')->willReturn($arr);

        $this->customerExtractorMock->expects($this->once())
            ->method('extract')
            ->willReturn($this->customerMock);

        $ext = $this->createMock(\Magento\Customer\Api\Data\CustomerExtensionInterface::class);
        $this->customerMock->method('getExtensionAttributes')->willReturn($ext);
        $this->customerMock->method('getId')->willReturn($ext);

        $exp = new InputException(
            new \Magento\Framework\Phrase('Customer email format error.')
        );

        $this->accountManagement->method('createAccount')->willThrowException($exp);

        $this->object->execute();
    }

    /**
     * @return void
     */
    public function testIsLoggedIn9()
    {
        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->cookieManagerMock->expects($this->at(0))->method('getCookie')
        ->with('x-id-token')
        ->will($this->returnValue("123"));

        $this->cookieManagerMock->expects($this->at(1))->method('getCookie')
        ->with('abt_cartKey')
        ->will($this->returnValue("432"));

        $this->awsHelperMock->method('getProfile')
        ->will($this->returnValue('
            {
                "status": true,
                "response" : {
                    "userInfo": {
                        "userName": "abc@abc.com",
                        "firstName": "ABC",
                        "lastName": "ABC",
                        "userType": "1"
                    },
                    "addresses": [
                        {
                            "country": "1",
                            "state": "Texas",
                            "lineOne": "AbC path",
                            "city": "Tex",
                            "zipCode": "87345"
                        }
                    ]
                }
            }
            '));

        $attr = $this->createMock(\Magento\Customer\Api\Data\AttributeMetadataInterface::class);
        $attr->method('getAttributeCode')->will($this->returnValue('region'));
        $arr[] = $attr;

        $this->formMock->method('getAllowedAttributes')->willReturn($arr);

        $this->customerExtractorMock->expects($this->any())
            ->method('extract')
            ->willReturn($this->customerMock);

        $ext = $this->createMock(\Magento\Customer\Api\Data\CustomerExtensionInterface::class);
        $this->customerMock->method('getExtensionAttributes')->willReturn($ext);
        $this->customerMock->method('getId')->willReturn($ext);

        $exp = new \Exception(
            new \Magento\Framework\Phrase('Exception')
        );

        $this->accountManagement->method('createAccount')->willThrowException($exp);

        $this->object->execute();
    }

    /**
     * @return void
     */
    public function testIsLoggedIn10()
    {
        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->cookieManagerMock->expects($this->at(0))->method('getCookie')
        ->with('x-id-token')
        ->will($this->returnValue("123"));

        $this->cookieManagerMock->expects($this->at(1))->method('getCookie')
        ->with('abt_cartKey')
        ->will($this->returnValue("432"));

        $this->awsHelperMock->method('getProfile')
        ->will($this->returnValue('
            {
                "status": true,
                "response" : {
                    "userInfo": {
                        "userName": "abc@abc.com",
                        "firstName": "ABC",
                        "lastName": "ABC",
                        "userType": "1"
                    },
                    "addresses": [
                        {
                            "country": "1",
                            "state": "Texas",
                            "lineOne": "AbC path",
                            "city": "Tex",
                            "zipCode": "87345"
                        }
                    ]
                }
            }
            '));

        $attr = $this->createMock(\Magento\Customer\Api\Data\AttributeMetadataInterface::class);
        $attr->method('getAttributeCode')->will($this->returnValue('region'));
        $arr[] = $attr;
        $this->formMock->method('getAllowedAttributes')->willReturn($arr);

        $this->customerExtractorMock->expects($this->once())
            ->method('extract')
            ->willReturn($this->customerMock);

        $ext = $this->createMock(\Magento\Customer\Api\Data\CustomerExtensionInterface::class);
        $this->customerMock->method('getExtensionAttributes')->willReturn($ext);
        $this->customerMock->method('getId')->willReturn($ext);

        $this->accountManagement->method('createAccount')->willReturn($this->customerMock);

        $accStatus = \Magento\Customer\Api\AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED;
        $this->accountManagement->method('getConfirmationStatus')->will($this->returnValue($accStatus));

        $this->object->execute();
    }

    /**
     * @return void
     */
    public function ttestCreateActionRegistrationEnabled()
    {
        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->registrationMock->expects($this->once())
            ->method('isAllowed')
            ->will($this->returnValue(true));

        $this->redirectMock->expects($this->never())
            ->method('redirect');

        $this->pageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultPageMock);

        $this->object->execute();
    }
}
