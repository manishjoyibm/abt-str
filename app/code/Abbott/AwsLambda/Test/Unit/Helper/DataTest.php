<?php

namespace Abbott\AwsLambda\Test\Unit\Helper;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    public $objectManager;
    /**
     * @var (\Abbott\AwsLambda\Logger\Log & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $logMock;
    public $scopeConfig;
    public $curl;
    /**
     * @var (\Magento\Framework\Encryption\EncryptorInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $encryptorInterfaceMock;
    public $customerRepository;
    public $dataMock;
    public function setUp():void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->logMock = $this->createMock(\Abbott\AwsLambda\Logger\Log::class);
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->curl = $this->createMock(\Magento\Framework\HTTP\Client\Curl::class, [], [], '', false);
        $this->encryptorInterfaceMock = $this->createMock(\Magento\Framework\Encryption\EncryptorInterface::class);

        $this->customerRepository = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);


        $this->dataMock = $this->objectManager->getObject(
            \Abbott\AwsLambda\Helper\Data::class,
            [
                'curl' => $this->curl,
                'scopeConfig' => $this->scopeConfig,
                'encryptor' => $this->encryptorInterfaceMock,
                'customerRepository' => $this->customerRepository
            ]
        );
    }

    /**
     * check it enable or not
     */
    public function testEnabled()
    {
        $testMethod = new \ReflectionMethod(\Abbott\AwsLambda\Helper\Data::class, 'enabled');
        $testMethod->setAccessible(true);

        $test = [
            'enable' => 1
        ];
        $this->scopeConfig->method('getValue')->will($this->returnValue($test['enable']));
        $this->assertEquals(
            $test['enable'],
            $testMethod->invokeArgs($this->dataMock, [$test['enable']]),
            "Check if the aws api is enabled"
        );
    }

    /**
     * test debug is enabled or not
     */
    public function testEnabledDebug()
    {
        $testMethod = new \ReflectionMethod(\Abbott\AwsLambda\Helper\Data::class, 'enabledDebug');
        $testMethod->setAccessible(true);

        $test = [
            'enable_debug' => 1
        ];


        $this->scopeConfig->method('getValue')->will($this->returnValue($test['enable_debug']));
        $this->assertEquals(
            $test['enable_debug'],
            $testMethod->invokeArgs($this->dataMock, [$test['enable_debug']]),
            "Check if the aws api debug is enabled"
        );
    }

    /**
     * test app id
     */
    public function testGetAppId()
    {
        $testMethod = new \ReflectionMethod(\Abbott\AwsLambda\Helper\Data::class, 'getAppId');
        $testMethod->setAccessible(true);

        $test = [
            'appid' => 'similacmbo'
        ];


        $this->scopeConfig->method('getValue')->will($this->returnValue($test['appid']));
        $this->assertEquals(
            $test['appid'],
            $testMethod->invokeArgs($this->dataMock, [$test['appid']]),
            "Check appid"
        );
    }

    /**
     * test app id
     */
    public function testGetUID()
    {
        $testMethod = new \ReflectionMethod(\Abbott\AwsLambda\Helper\Data::class, 'getUID');
        $testMethod->setAccessible(true);

        $test = [
            'uid' => 'similacmbo'
        ];


        $this->scopeConfig->method('getValue')->will($this->returnValue($test['uid']));
        $this->assertEquals(
            $test['uid'],
            $testMethod->invokeArgs($this->dataMock, [$test['uid']]),
            "get uid"
        );
    }

    /**
     * @return string
     * test access key
     **/
    public function testGetAccessKey()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var $encryptor EncryptorInterface */
        $encryptor = $objectManager->get(\Magento\Framework\Encryption\EncryptorInterface::class);

        $testMethod = new \ReflectionMethod(\Abbott\AwsLambda\Helper\Data::class, 'getAccessKey');
        $testMethod->setAccessible(true);

        $test = [
            'accesskey' => 'vP+Py29WHTEzTkyApjRLA6bobOaQUBcKp5ssST5FqTp/0Qo='
        ];

        $this->scopeConfig->method('getValue')->will($this->returnValue($test['accesskey']));

        $this->assertEquals(
            $encryptor->getHash($test['accesskey']),
            $testMethod->invokeArgs($this->dataMock, [$test['accesskey']]),
            "get accesskey"
        );
    }

    /**
     * get profile url
     */
    public function testGetPostUrl()
    {
        $testMethod = new \ReflectionMethod(\Abbott\AwsLambda\Helper\Data::class, 'getPostUrl');
        $testMethod->setAccessible(true);

        $test = [
            'posturl' => 'https://dev.similac.com/api/system/profile/update-profile-info'
        ];

        $this->scopeConfig->method('getValue')->will($this->returnValue($test['posturl']));
        $this->assertEquals(
            $test['posturl'],
            $testMethod->invokeArgs($this->dataMock, [$test['posturl']]),
            "get post url"
        );
    }

    /**
     * set store id
     */
    public function testSetStoreId()
    {
        $testMethod = new \ReflectionMethod(\Abbott\AwsLambda\Helper\Data::class, 'setStoreId');
        $testMethod->setAccessible(true);

        $test = [
            'storeid' => 4
        ];

        $this->assertEquals(
            $test['storeid'],
            $testMethod->invokeArgs($this->dataMock, [$test['storeid']]),
            "set storeid"
        );
    }
    /**
     * check it enable or not
     */
    public function testIsCreateCustomerEnabled()
    {
        $testMethod = new \ReflectionMethod(\Abbott\AwsLambda\Helper\Data::class, 'isCreateCustomerEnabled');
        $testMethod->setAccessible(true);

        $test = [
            'enable_customer_creation' => 1
        ];
        $this->scopeConfig->method('getValue')->will($this->returnValue($test['enable_customer_creation']));
        $this->assertEquals(
            $test['enable_customer_creation'],
            $testMethod->invokeArgs($this->dataMock, [$test['enable_customer_creation']]),
            "Check if the customer creation is enabled"
        );
    }

    /**
     * test access key
     */
    public function testGetProfileApiUrl()
    {
        $testMethod = new \ReflectionMethod(\Abbott\AwsLambda\Helper\Data::class, 'getProfileApiUrl');
        $testMethod->setAccessible(true);

        $test = [
            'get_profile_url' => 'https://dev.similac.com/api/system/profile/get-profile-info'
        ];

        $this->scopeConfig->method('getValue')->will($this->returnValue($test['get_profile_url']));
        $this->assertEquals(
            $test['get_profile_url'],
            $testMethod->invokeArgs($this->dataMock, [$test['get_profile_url']]),
            "get post url"
        );
    }

    /**
     * test secret
     */
    public function testGetApppOriginSecret()
    {
        $testMethod = new \ReflectionMethod(\Abbott\AwsLambda\Helper\Data::class, 'getApppOriginSecret');
        $testMethod->setAccessible(true);

        $test = [
            'x_origin_secret' => 'c5b292d1290fce1c463af73ead3897a8'
        ];

        $this->scopeConfig->method('getValue')->will($this->returnValue($test['x_origin_secret']));
        $this->assertEquals(
            $test['x_origin_secret'],
            $testMethod->invokeArgs($this->dataMock, [$test['x_origin_secret']]
            ),
            "get x origin"
        );
    }

    /**
     * test profile
     */
    public function testGetProfile()
    {
        $testMethod = new \ReflectionMethod(\Abbott\AwsLambda\Helper\Data::class, 'getProfile');
        $testMethod->setAccessible(true);

        $test = [
            'get_profile_url' => 'https://dev.similac.com/api/system/profile/get-profile-info'
        ];

        $this->scopeConfig->method('getValue')->will($this->returnValue($test['get_profile_url']));

        $test1 = [
            'appid' => 'similacmbo'
        ];


        $this->scopeConfig->method('getValue')->will($this->returnValue($test1['appid']));


        $this->assertEquals(null, $this->dataMock->getProfile('assddd', []));
    }

    /**
     * test profile
     */
    public function testSetAwsHeader()
    {
        $this->dataMock->setAwsHeader($this->curl);
    }

    /**
     * test get gigya uid by customer id
     */
    public function testGetGigyaUid()
    {
        $returnval = '123456789';
        $customerData = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMockForAbstractClass();

        $this->customerRepository
            ->expects($this->any())
            ->method('getById')
            ->with(1)
            ->will($this->returnValue($customerData));

        $attributeMock = $this->getMockBuilder(\Magento\Framework\Api\AbstractExtensibleObject::class)
        ->setMethods(['getValue'])
        ->disableOriginalConstructor()
        ->getMockForAbstractClass();

        $customerData->method('getCustomAttribute')->with('gigya_uid')->willReturn($attributeMock);

        $attributeMock->expects($this->any())
            ->method('getValue')
            ->willReturn($returnval);
        $this->assertSame($returnval, $this->dataMock->getGigyaUid(1));
    }
}
